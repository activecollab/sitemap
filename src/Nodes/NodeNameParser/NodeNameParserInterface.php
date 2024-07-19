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
}
