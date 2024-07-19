<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\Location;

interface SitemapLocationInterface
{
    public function getSitemapPath(): string;
    public function getNodePath(string $nodeName): string;
}
