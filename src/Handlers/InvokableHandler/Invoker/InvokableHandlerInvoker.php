<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\Handlers\InvokableHandler\Invoker;

use ActiveCollab\Sitemap\Location\SitemapLocationInterface;
use ActiveCollab\Sitemap\Handlers\Handler;
use ActiveCollab\Sitemap\Handlers\InvokableHandler\InvokableHandlerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class InvokableHandlerInvoker extends Handler implements InvokableHandlerInvokerInterface
{
    public function __construct(
        private ContainerInterface $container,
        private SitemapLocationInterface $sitemapLocation,
        private string $invokableFilePath,
    )
    {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
    ): ResponseInterface
    {
        $inclusionResult = include $this->sitemapLocation->getNodePath($this->invokableFilePath);

        if ($inclusionResult instanceof InvokableHandlerInterface) {
            $inclusionResult->setContainer($this->container);

            return $inclusionResult($request, $response);
        }

        return $response;
    }
}
