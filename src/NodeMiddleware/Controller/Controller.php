<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\NodeMiddleware\Controller;

use Doctrine\Inflector\Inflector;
use ActiveCollab\Sitemap\NodeMiddleware\NodeMiddleware;
use ActiveCollab\Sitemap\NodeMiddleware\NodeMiddlewareInterface;
use ActiveCollab\Sitemap\Sitemap\SitemapInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Slim\Routing\Route;

abstract class Controller extends NodeMiddleware implements ControllerInterface
{
    private ?RequestHandlerInterface $handler = null;
    private string $absoluteMiddlewarePath;

    public function __construct(string $absoluteMiddlewarePath)
    {
        $this->absoluteMiddlewarePath = $absoluteMiddlewarePath;

        parent::__construct();
    }

    protected function before(ServerRequestInterface $request): ServerRequestInterface|ResponseInterface
    {
        return $request;
    }

    protected function proceedToNode(ServerRequestInterface $request): ResponseInterface
    {
        return $this->handler->handle($request);
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface
    {
        if ($this->handler !== null) {
            throw new RuntimeException('Controller can be executed only once.');
        }

        $this->handler = $handler;

        $request = $this->before($request);

        if ($request instanceof ResponseInterface) {
            return $request;
        }

        $actionName = $this->getActionNameFromRequest($request);

        if (empty($actionName)) {
            return $handler->handle($request);
        }

        return $this->mustExecuteAction($actionName, $request);
    }

    private function mustExecuteAction(
        string $actionName,
        ServerRequestInterface $request,
    ): ResponseInterface
    {
        if (!method_exists($this, $actionName)) {
            throw new RuntimeException(sprintf('Action "%s" does not exist.', $actionName));
        }

        return call_user_func([$this, $actionName], $request);
    }

    private function getActionNameFromRequest(ServerRequestInterface $request): ?string
    {
        $route = $request->getAttribute(NodeMiddlewareInterface::DEFAULT_ROUTE_KEY);

        if (!$route instanceof Route) {
            return null;
        }

        if (!$this->isControllerRoute($route)) {
            return null;
        }

        $nodeName = $route->getArgument(SitemapInterface::NODE_NAME_ROUTE_ARGUMENT);

        if (empty($nodeName)) {
            return null;
        }

        $inflector = $this->get(Inflector::class);

        $actionName = sprintf(
            '%sAction',
            $inflector->camelize($nodeName),
        );

        // Action exists.
        if (method_exists($this, $actionName)) {
            return $actionName;
        }

        // Action was not found, but node does exist.
        if ($this->nodeExistsAtMiddlewarePath($nodeName)) {
            return 'proceedToNode';
        }

        throw new RuntimeException(sprintf('Action "%s" does not exist.', $actionName));
    }

    private function nodeExistsAtMiddlewarePath(string $nodeName): bool
    {
        return !empty(
            glob(
                sprintf(
                    '%s/%s.*',
                    dirname($this->absoluteMiddlewarePath),
                    $nodeName,
                )
            )
        );
    }

    private function isControllerRoute(Route $route): bool
    {
        $nodePath = $route->getArgument(SitemapInterface::NODE_PATH_ROUTE_ARGUMENT);

        return sprintf('/%s', dirname(ltrim($nodePath, '/'))) ===
            sprintf('/%s', dirname(ltrim($this->getMiddlewarePath(), '/')));
    }

    private ?string $middlewarePath = null;

    protected function getMiddlewarePath(): string
    {
        if ($this->middlewarePath === null) {
            $sitemap = $this->get(SitemapInterface::class);

            $this->middlewarePath = mb_substr(
                $this->absoluteMiddlewarePath,
                mb_strlen($sitemap->getSitemapPath()) + 1,
            );
        }

        return $this->middlewarePath;
    }
}
