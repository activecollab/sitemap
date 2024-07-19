<?php

/*
 * This file is part of the Active Collab Bootstrap project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\Nodes\File;

use ActiveCollab\Sitemap\Nodes\NodeInterface;

interface FileInterface extends NodeInterface
{
    public function isIndex(): bool;
    public function isMiddleware(): bool;
    public function getExtension(): string;
    public function isExecutable(): bool;
}
