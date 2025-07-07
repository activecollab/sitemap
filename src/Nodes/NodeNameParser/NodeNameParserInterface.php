<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\Nodes\NodeNameParser;

interface NodeNameParserInterface
{
    public function getFileProperties(): array;
    public function getDirectoryProperties(): array;
    public function getNodeName(): string;
    public function getExtension(): string;
    public function isHidden(): bool;
    public function isSystem(): bool;
    public function isVariable(): bool;
}
