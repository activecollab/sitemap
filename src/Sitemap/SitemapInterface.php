<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\Sitemap;

use Psr\Container\ContainerInterface;
use Slim\Interfaces\RouteCollectorProxyInterface;

interface SitemapInterface
{
    const NODE_NAME_ROUTE_ARGUMENT = 'nodeName';
    const NODE_PATH_ROUTE_ARGUMENT = 'nodePath';

    public function getSitemapPath(): string;
    public function urlFor(string $routeName, array $data = []): string;
    public function absoluteUrlFor(string $routeName, array $data = []): string;
    public function isLoaded(): bool;
    public function loadRoutes(
        RouteCollectorProxyInterface $app,
        ContainerInterface $container,
    ): iterable;
    public function getLoadedRoutes(): iterable;
}
