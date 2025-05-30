<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\Nodes\File;

use ActiveCollab\Sitemap\Nodes\Node;
use ActiveCollab\Sitemap\Nodes\NodeNameParser\NodeNameParser;

class File extends Node implements FileInterface
{
    private $node_name = '';
    private $extension = '';
    private $is_hidden = false;
    private $is_system = false;
    private $is_variable = false;

    public function __construct(string $routing_root, string $node_path)
    {
        parent::__construct($routing_root, $node_path);

        [
            $this->node_name,
            $this->extension,
            $this->is_hidden,
            $this->is_system,
            $this->is_variable,
        ] = (new NodeNameParser($this->getBasename()))->getFileProperties();
    }

    public function getNodeName(): string
    {
        return $this->node_name;
    }

    public function isIndex(): bool
    {
        return $this->getNodeName() === 'index';
    }

    public function isMiddleware(): bool
    {
        return $this->isSystem()
            && $this->getNodeName() === 'middleware';
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function isHidden(): bool
    {
        return $this->is_hidden;
    }

    public function isExecutable(): bool
    {
        return $this->extension === 'php';
    }

    public function isSystem(): bool
    {
        return $this->is_system;
    }

    public function isVariable(): bool
    {
        return $this->is_variable;
    }
}
