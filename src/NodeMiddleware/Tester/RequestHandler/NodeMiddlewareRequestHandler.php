<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\NodeMiddleware\Tester\RequestHandler;

use Laminas\Diactoros\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class NodeMiddlewareRequestHandler implements NodeMiddlewareRequestHandlerInterface
{
    private ?ServerRequestInterface $capturedRequest;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->capturedRequest = $request;

        return (new ResponseFactory())->createResponse();
    }

    public function getCapturedRequest(): ?ServerRequestInterface
    {
        return $this->capturedRequest;
    }
}
