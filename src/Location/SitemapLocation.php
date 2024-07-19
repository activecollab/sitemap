<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\Location;

class SitemapLocation implements SitemapLocationInterface
{
    public function __construct(
        private string $sitemapPath,
    )
    {
    }

    public function getSitemapPath(): string
    {
        return $this->sitemapPath;
    }

    public function getNodePath(string $nodeName): string
    {
        return sprintf(
            '%s/%s',
            $this->sitemapPath,
            $nodeName,
        );
    }
}
