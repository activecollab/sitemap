<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\Pathfinder;

use ActiveCollab\Sitemap\Handlers\HandlerInterface;
use ActiveCollab\Sitemap\Nodes\File\FileInterface;
use ActiveCollab\Sitemap\Nodes\NodeInterface;
use ActiveCollab\Sitemap\Pathfinder\NodeHandlerDetector\NodeHandlerDetectorInterface;

class Pathfinder implements PathfinderInterface
{
    private array $nodeHandlerDetectors;

    public function __construct(NodeHandlerDetectorInterface ...$nodeHandlerDetectors)
    {
        $this->nodeHandlerDetectors = $nodeHandlerDetectors;
    }

    public function hasRoute(NodeInterface $node): bool
    {
        return !$node->isSystem() && !$node->isHidden();
    }

    public function getRoutingPath(NodeInterface ...$nodes): ?string
    {
        $last_node = end($nodes);

        if ($last_node instanceof NodeInterface && !$this->hasRoute($last_node)) {
            return null;
        }

        $path = [];

        foreach ($nodes as $node) {
            if (!$this->hasRoute($node)) {
                return null;
            }

            $path[] = $this->getRoutingPathForNode($node);
        }

        return '/' . trim(implode('/', $path), '/');
    }

    private function getRoutingPathForNode(NodeInterface $node): string
    {
        if ($node instanceof FileInterface && $node->isIndex()) {
            return '/';
        }

        if ($node->isVariable()) {
            return sprintf('{%s}', $node->getNodeName());
        }

        return $node->getNodeName();
    }

    public function getRouteHandler(NodeInterface $node): ?HandlerInterface
    {
        if (!$this->hasRoute($node)) {
            return null;
        }

        foreach ($this->nodeHandlerDetectors as $nodeHandlerDetector) {
            $handler = $nodeHandlerDetector->probe($node);

            if ($handler) {
                return $handler;
            }
        }

        return null;
    }
}
