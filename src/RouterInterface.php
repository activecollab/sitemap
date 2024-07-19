<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap;

use ActiveCollab\Sitemap\Nodes\Directory\DirectoryInterface;

interface RouterInterface
{
    public function scan(string $routingRoot): DirectoryInterface;
}
