<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\NodeMiddleware;

abstract class NodeMiddleware implements NodeMiddlewareInterface
{
    use NodeMiddlewareTrait;

    public function __construct()
    {
        $this->configure();
    }

    protected function configure(): void
    {
    }
}
