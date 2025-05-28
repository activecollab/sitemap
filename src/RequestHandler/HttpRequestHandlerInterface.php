<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\RequestHandler;

use JsonSerializable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface HttpRequestHandlerInterface
{
    const DEFAULT_POST_OVERRIDE_FIELD_NAME = '__post_method_override';

    public function ok(string $reasonPhrase = ''): ResponseInterface;
    public function badRequest(string $reasonPhrase = ''): ResponseInterface;
    public function forbidden(string $reasonPhrase = ''): ResponseInterface;
    public function notFound(string $reasonPhrase = ''): ResponseInterface;
    public function conflict(string $reasonPhrase = ''): ResponseInterface;
    public function internalError(string $reasonPhrase = ''): ResponseInterface;
    public function serviceUnavailable(string $reasonPhrase = ''): ResponseInterface;

    public function status(
        int $code,
        string $reasonPhrase = '',
        ResponseInterface $response = null
    ): ResponseInterface;

    public function json(
        JsonSerializable|array $data,
        int $statusCode = 200,
        ResponseInterface $response = null,
    ): ResponseInterface;

    public function created(
        JsonSerializable|array $entity,
        ResponseInterface $response = null,
    ): ResponseInterface;

    public function moved(
        string $url,
        bool $isMovedPermanently = false,
        ResponseInterface $response = null
    ): ResponseInterface;

    public function movedToRoute(
        string $routeName,
        array $data = [],
        bool $isMovedPermanently = false,
        ResponseInterface $response = null
    ): ResponseInterface;

    public function redirect(
        ServerRequestInterface $request,
        string $url,
        ResponseInterface $response = null,
    ): ResponseInterface;

    public function redirectToRoute(
        ServerRequestInterface $request,
        string $routeName,
        array $data = [],
        ResponseInterface $response = null,
    ): ResponseInterface;

    public function renderContent(
        string $content,
        int $statusCode = 200,
        string $reasonPhrase = null,
        ResponseInterface $response = null,
    ): ResponseInterface;

    public function renderTemplate(
        string $templatePath,
        array $templateVariables = [],
        int $statusCode = 200,
        string $reasonPhrase = null,
        ResponseInterface $response = null,
    ): ResponseInterface;
}
