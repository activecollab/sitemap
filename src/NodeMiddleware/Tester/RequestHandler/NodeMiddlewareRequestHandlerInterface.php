<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\NodeMiddleware\Tester\RequestHandler;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface NodeMiddlewareRequestHandlerInterface extends RequestHandlerInterface
{
    public function getCapturedRequest(): ?ServerRequestInterface;
}
