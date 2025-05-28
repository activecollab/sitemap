<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\NodeMiddleware;

use ActiveCollab\ContainerAccess\ContainerAccessInterface\Implementation as ContainerAccessImplementation;
use ActiveCollab\Sitemap\RequestHandler\HttpRequestHandlerTrait;
use ActiveCollab\Sitemap\RoutingContext\RoutingContextInterface;
use ActiveCollab\Sitemap\Sitemap\SitemapInterface;
use ActiveCollab\TemplateEngine\TemplateEngineInterface;
use Laminas\Diactoros\ResponseFactory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Slim\Interfaces\RouteInterface;

abstract class NodeMiddleware implements NodeMiddlewareInterface
{
    use HttpRequestHandlerTrait;
    use ContainerAccessImplementation;

    private string $routeKey = NodeMiddlewareInterface::DEFAULT_ROUTE_KEY;

    public function __construct()
    {
        $this->configure();
    }

    protected function configure(): void
    {
    }

    public function getRouteKey(): string
    {
        return $this->routeKey;
    }

    protected function setRouteKey(string $routeKey): NodeMiddlewareInterface
    {
        $this->routeKey = $routeKey;

        return $this;
    }

    protected function getRoute(ServerRequestInterface $request): RouteInterface
    {
        $route = $request->getAttribute($this->routeKey);

        if (!$route instanceof RouteInterface) {
            throw new RuntimeException('Failed to find route in request.');
        }

        return $route;
    }

    protected function isRoute(
        ServerRequestInterface $request,
        string $nodeName,
        string $requestMethod = null,
    ): bool
    {
        $route = $this->getRoute($request);

        if ($requestMethod !== null && $request->getMethod() !== $requestMethod) {
            return false;
        }

        if ($route->getArgument(SitemapInterface::NODE_NAME_ROUTE_ARGUMENT) !== $nodeName) {
            return false;
        }

        return true;
    }

    protected function isTypeRoute(
        ServerRequestInterface $request,
        RoutingContextInterface $context,
        string $nodeName = 'index',
        string $requestMethod = null,
    ): bool
    {
        if ($requestMethod !== null && $request->getMethod() !== $requestMethod) {
            return false;
        }

        return sprintf(
                '%s_%s',
                $context->getRoutePrefix(),
                $nodeName
            ) === $this->getRoute($request)->getName();
    }

    private ResponseFactoryInterface $responseFactory;

    protected function getResponseFactory(): ResponseFactoryInterface
    {
        if (empty($this->responseFactory)) {
            $this->responseFactory = new ResponseFactory();
        }

        return $this->responseFactory;
    }

    protected function getSitemap(): SitemapInterface
    {
        return $this->getContainer()->get(SitemapInterface::class);
    }

    protected function getTemplateEngine(): TemplateEngineInterface
    {
        return $this->get(TemplateEngineInterface::class);
    }

    /**
     * @template TClassName
     * @param  class-string<TClassName> $id
     * @return TClassName
     */
    public function get(string $id): mixed
    {
        return $this->getContainer()->get($id);
    }
}
