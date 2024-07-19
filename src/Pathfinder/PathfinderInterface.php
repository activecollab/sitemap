<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\Pathfinder;

use ActiveCollab\Sitemap\Handlers\HandlerInterface;
use ActiveCollab\Sitemap\Nodes\NodeInterface;

interface PathfinderInterface
{
    public function hasRoute(NodeInterface $node): bool;
    public function getRoutingPath(NodeInterface ...$nodes): ?string;
    public function getRouteHandler(NodeInterface $node): ?HandlerInterface;
}
