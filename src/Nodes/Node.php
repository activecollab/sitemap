<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\Nodes;

abstract class Node implements NodeInterface
{
    private $routing_root;
    private $node_path;
    private $path;
    private $basename;

    public function __construct(
        string $routing_root,
        string $node_path
    )
    {
        $this->routing_root = $routing_root;
        $this->node_path = $node_path;

        $this->path = sprintf('%s/%s', $this->routing_root, $this->node_path);
        $this->basename = basename($this->path);
    }

    public function getRoutingRoot(): string
    {
        return $this->routing_root;
    }

    public function getNodePath(): string
    {
        return $this->node_path;
    }

    public function getBasename(): string
    {
        return $this->basename;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
