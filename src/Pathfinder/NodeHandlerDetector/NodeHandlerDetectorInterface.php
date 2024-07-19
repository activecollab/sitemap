<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\Pathfinder\NodeHandlerDetector;

use ActiveCollab\Sitemap\Handlers\HandlerInterface;
use ActiveCollab\Sitemap\Nodes\NodeInterface;

interface NodeHandlerDetectorInterface
{
    public function probe(NodeInterface $node): ?HandlerInterface;
}
