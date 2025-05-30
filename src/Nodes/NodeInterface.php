<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\Nodes;

interface NodeInterface
{
    public function getRoutingRoot(): string;
    public function getNodeName(): string;
    public function getNodePath(): string;
    public function getBasename(): string;
    public function getPath(): string;

    public function isHidden(): bool;
    public function isSystem(): bool;
    public function isVariable(): bool;
}
