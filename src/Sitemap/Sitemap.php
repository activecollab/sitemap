<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\Sitemap;

use ActiveCollab\ContainerAccess\ContainerAccessInterface;
use ActiveCollab\Sitemap\Location\SitemapLocationInterface;
use FastRoute\RouteParser\Std;
use InvalidArgumentException;
use LogicException;
use ActiveCollab\Sitemap\NodeMiddleware\Controller\Factory\ControllerFactoryInterface;
use ActiveCollab\Sitemap\Nodes\Directory\DirectoryInterface;
use ActiveCollab\Sitemap\Nodes\File\FileInterface;
use ActiveCollab\Sitemap\Pathfinder\PathfinderInterface;
use ActiveCollab\Sitemap\Router;
use ActiveCollab\Url\UrlInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use RuntimeException;
use Slim\Interfaces\RouteCollectorProxyInterface;
use Slim\Interfaces\RouteInterface;

class Sitemap implements SitemapInterface
{
    private array $loadedRoutes = [];
    private bool $isLoaded = false;

    public function __construct(
        private SitemapLocationInterface $sitemapLocation,
        private PathfinderInterface $pathfinder,
        private UrlInterface $rootUrl,
    )
    {
    }

    public function getSitemapPath(): string
    {
        return $this->sitemapLocation->getSitemapPath();
    }

    public function getLoadedRoutes(): iterable
    {
        return $this->loadedRoutes;
    }

    public function urlFor(string $routeName, array $data = []): string
    {
        $route = $this->mustGetLoadedRoute($routeName);
        $routeParser = new Std();
        $pattern = $route->getPattern();

        $segments = [];
        $segmentName = '';

        $expressions = array_reverse($routeParser->parse($pattern));
        foreach ($expressions as $expression) {
            foreach ($expression as $segment) {
                if (is_string($segment)) {
                    $segments[] = $segment;
                    continue;
                }

                if (!array_key_exists($segment[0], $data)) {
                    $segments = [];
                    $segmentName = $segment[0];
                    break;
                }

                $segments[] = $data[$segment[0]];

                unset($data[$segment[0]]);
            }

            if (!empty($segments)) {
                break;
            }
        }

        if (empty($segments)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Missing data for URL segment: %s',
                    $segmentName
                )
            );
        }

        $url = implode('', $segments);
        if (!empty($data)) {
            $url .= '?' . http_build_query($data);
        }

        return $url;
    }

    public function absoluteUrlFor(string $routeName, array $data = []): string
    {
        return sprintf('%s/%s',
            $this->rootUrl->getUrl(),
            ltrim($this->urlFor($routeName, $data), '/')
        );
    }

    public function loadRoutes(
        RouteCollectorProxyInterface $app,
        ContainerInterface $container,
    ): iterable
    {
        if ($this->isLoaded) {
            throw new LogicException('Sitemap already loaded.');
        }

        $rootDir = (new Router())->scan($this->sitemapLocation->getSitemapPath());

        $group = $app->group(
            '/',
            function (RouteCollectorProxyInterface $proxy) use ($rootDir, $container) {
                if ($rootDir->hasIndex()) {
                    $handler = $this->pathfinder->getRouteHandler($rootDir->getIndex());

                    if ($handler) {
                        $this->registerLoadedRoute(
                            $proxy->any(
                                ltrim($this->pathfinder->getRoutingPath($rootDir->getIndex()), '/'),
                                $handler
                            )
                                ->setName('index')
                                ->setArgument(
                                    self::NODE_NAME_ROUTE_ARGUMENT,
                                    $rootDir->getIndex()->getNodeName()
                                )
                                ->setArgument(
                                    self::NODE_PATH_ROUTE_ARGUMENT,
                                    $rootDir->getIndex()->getNodePath()
                                )
                        );
                    }
                }

                foreach ($rootDir->getFiles() as $file) {
                    if ($file->isIndex()) {
                        continue;
                    }

                    $handler = $this->pathfinder->getRouteHandler($file);

                    if ($handler) {
                        $this->registerLoadedRoute(
                            $proxy->any(
                                ltrim($this->pathfinder->getRoutingPath($file), '/'),
                                $handler
                            )
                                ->setName(str_replace('-', '_', $file->getNodeName()))
                                ->setArgument(
                                    self::NODE_NAME_ROUTE_ARGUMENT,
                                    $file->getNodeName()
                                )
                                ->setArgument(
                                    self::NODE_PATH_ROUTE_ARGUMENT,
                                    $file->getNodePath()
                                )
                        );
                    }
                }

                foreach ($rootDir->getSubdirectories() as $subSubdirectory) {
                    if ($subSubdirectory->isSystem()) {
                        continue;
                    }

                    $this->loadSubdirectoryRoutes(
                        $proxy,
                        $subSubdirectory,
                        $container,
                        $subSubdirectory->getNodeName(),
                        false,
                    );
                }
            }
        );

        foreach ($this->loadMiddlewares($rootDir, $container) as $middleware) {
            $group->add($middleware);
        }

        $this->isLoaded = true;

        return $this->loadedRoutes;
    }

    protected function loadSubdirectoryRoutes(
        RouteCollectorProxyInterface $collector,
        DirectoryInterface $subdirectory,
        ContainerInterface $container,
        string $routePrefix,
        bool $prefixGroupPath
    ): void
    {
        $group = $collector->group(
            $this->getGroupPattern($subdirectory, $prefixGroupPath),
            function (RouteCollectorProxyInterface $groupCollectorProxy) use ($subdirectory, $routePrefix, $container) {
                foreach ($subdirectory->getSubdirectories() as $subSubdirectory) {
                    if ($subSubdirectory->isSystem()) {
                        continue;
                    }

                    $this->loadSubdirectoryRoutes(
                        $groupCollectorProxy,
                        $subSubdirectory,
                        $container,
                        $routePrefix . '_' . $subSubdirectory->getNodeName(),
                        true,
                    );
                }

                if ($subdirectory->hasIndex()) {
                    $handler = $this->pathfinder->getRouteHandler($subdirectory->getIndex());

                    if ($handler) {
                        $this->registerLoadedRoute(
                            $groupCollectorProxy->any('[/]', $handler)
                                ->setName($routePrefix . '_index')
                                ->setArgument(
                                    self::NODE_NAME_ROUTE_ARGUMENT,
                                    $subdirectory->getIndex()->getNodeName()
                                )
                                ->setArgument(
                                    self::NODE_PATH_ROUTE_ARGUMENT,
                                    $subdirectory->getIndex()->getNodePath()
                                )
                        );
                    }
                }

                foreach ($subdirectory->getFiles() as $file) {
                    if ($file->isIndex()) {
                        continue;
                    }

                    $handler = $this->pathfinder->getRouteHandler($file);

                    if ($handler) {
                        $this->registerLoadedRoute(
                            $groupCollectorProxy->any(
                                $this->pathfinder->getRoutingPath($file),
                                $handler
                            )
                                ->setName($this->getRouteName($file, $routePrefix))
                                ->setArgument(
                                    self::NODE_NAME_ROUTE_ARGUMENT,
                                    $file->getNodeName()
                                )
                                ->setArgument(
                                    self::NODE_PATH_ROUTE_ARGUMENT,
                                    $file->getNodePath()
                                )
                        );
                    }
                }
            }
        );

        foreach ($this->loadMiddlewares($subdirectory, $container) as $middleware) {
            $group->add($middleware);
        }
    }

    private function getGroupPattern(DirectoryInterface $directory, bool $prefixGroupPath): string
    {
        $result = $prefixGroupPath ? '/' : '';

        if ($directory->isVariable()) {
            $result .= $this->getVariableGroupPattern($directory);
        } else {
            $result .= $directory->getNodeName();
        }

        return $result;
    }

    private function getVariableGroupPattern(DirectoryInterface $directory): string
    {
        $nodeNameBits = explode('_', $directory->getNodeName());
        $lastNodeNameBit = $nodeNameBits[count($nodeNameBits) - 1];

        $modifier = $this->getModifierFromLastNodeNameBit($lastNodeNameBit);

        $patternBits = [
            $directory->getNodeName(),
        ];

        if ($modifier) {
            $patternBits[] = $modifier;
        }

        return sprintf('{%s}', implode(':', $patternBits));
    }

    private function getModifierFromLastNodeNameBit(string $lastNodeNameBit): string
    {
        if ($lastNodeNameBit === 'id') {
            return '[0-9]+';
        }

        return '';
    }

    private function getRouteName(
        FileInterface $file,
        string $routePrefix,
    ): string
    {
        return sprintf('%s_%s',
            $routePrefix,
            str_replace('-', '_', $file->getNodeName())
        );
    }

    protected function loadMiddlewares(
        DirectoryInterface $directory,
        ContainerInterface $container,
    ): iterable
    {
        $middlewareNode = $directory->getMiddleware();

        if (empty($middlewareNode)) {
            return;
        }

        // Make controller factory available in __middleware files.
        $controllerFactory = $container->get(ControllerFactoryInterface::class);

        $middlewares = require $middlewareNode->getPath();

        if (empty($middlewares) || !is_array($middlewares)) {
            return;
        }

        foreach ($middlewares as $middleware) {
            if (!$middleware instanceof MiddlewareInterface) {
                break;
            }

            if ($middleware instanceof ContainerAccessInterface) {
                $middleware->setContainer($container);
            }

            yield $middleware;
        }
    }

    private function getLoadedRoute(string $routeName): ?RouteInterface
    {
        return $this->loadedRoutes[$routeName] ?? null;
    }

    private function mustGetLoadedRoute(string $routeName): RouteInterface
    {
        $route = $this->getLoadedRoute($routeName);

        if (empty($route)) {
            throw new RuntimeException(sprintf('Route "%s" not found.', $routeName));
        }

        return $route;
    }

    private function registerLoadedRoute(RouteInterface $route): void
    {
        $this->loadedRoutes[$route->getName()] = $route;
    }

    public function isLoaded(): bool
    {
        return $this->isLoaded;
    }
}
