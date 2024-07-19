<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\RoutingContext;

interface RoutingContextInterface
{
    public function getUrl(string $subpageName = null, array $data = []): string;
    public function getRoutePrefix(): string;
    public function getRouteData(): array;
}
