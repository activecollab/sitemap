<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\NodeMiddleware\Tester;

use ActiveCollab\Sitemap\NodeMiddleware\Tester\Result\NodeMiddlewareTestResultInterface;

interface NodeMiddlewareTesterInterface
{
    public function runTest(
        string $middlewareClass,
        array $middlewareConstructorArgs,
        string $uri,
        string $requestMethod,
        string $nodeName,
        array $attributes = null,
        $parsedBody = null
    ): NodeMiddlewareTestResultInterface;
}
