<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\NodeMiddleware\Tester\Result;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface NodeMiddlewareTestResultInterface
{
    public function getRequest(): ServerRequestInterface;
    public function getResponse(): ResponseInterface;
}
