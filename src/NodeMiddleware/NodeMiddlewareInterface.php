<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\NodeMiddleware;

use ActiveCollab\ContainerAccess\ContainerAccessInterface;
use ActiveCollab\Sitemap\RequestHandler\HttpRequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

interface NodeMiddlewareInterface extends HttpRequestHandlerInterface, ContainerAccessInterface, MiddlewareInterface
{
    const DEFAULT_ROUTE_KEY = '__route__';

    public function getRouteKey(): string;
}
