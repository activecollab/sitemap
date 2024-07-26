<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\NodeMiddleware;

use ActiveCollab\ContainerAccess\ContainerAccessInterface\Implementation as ContainerAccessImplementation;
use ActiveCollab\Sitemap\NodeMiddleware\TemplateVariablesProcessor\TemplateVariablesProcessor;
use ActiveCollab\Sitemap\NodeMiddleware\TemplateVariablesProcessor\TemplateVariablesProcessorInterface;
use ActiveCollab\TemplateEngine\TemplateEngineInterface;
use InvalidArgumentException;
use JsonSerializable;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\Stream;
use ActiveCollab\Sitemap\RoutingContext\RoutingContextInterface;
use ActiveCollab\Sitemap\Sitemap\SitemapInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Slim\Interfaces\RouteInterface;

trait NodeMiddlewareTrait
{
    use ContainerAccessImplementation;

    private string $routeKey = NodeMiddlewareInterface::DEFAULT_ROUTE_KEY;
    private SitemapInterface $sitemap;

    public function getRouteKey(): string
    {
        return $this->routeKey;
    }

    protected function setRouteKey(string $routeKey): NodeMiddlewareInterface
    {
        $this->routeKey = $routeKey;

        return $this;
    }

    private ?TemplateVariablesProcessorInterface $templateVariablesProcessor = null;

    public function getTemplateVariablesProcessor(): TemplateVariablesProcessorInterface
    {
        if (empty($this->templateVariablesProcessor)) {
            $this->templateVariablesProcessor = new TemplateVariablesProcessor();
        }

        return $this->templateVariablesProcessor;
    }

    public function ok(string $reasonPhrase = ''): ResponseInterface
    {
        return $this->status(200, $reasonPhrase);
    }

    public function badRequest(string $reasonPhrase = ''): ResponseInterface
    {
        return $this->status(400, $reasonPhrase);
    }

    public function forbidden(string $reasonPhrase = ''): ResponseInterface
    {
        return $this->status(403, $reasonPhrase);
    }

    public function notFound(string $reasonPhrase = ''): ResponseInterface
    {
        return $this->status(404, $reasonPhrase);
    }

    public function conflict(string $reasonPhrase = ''): ResponseInterface
    {
        return $this->status(409, $reasonPhrase);
    }

    public function internalError(string $reasonPhrase = ''): ResponseInterface
    {
        return $this->status(500, $reasonPhrase);
    }

    public function serviceUnavailable(string $reasonPhrase = ''): ResponseInterface
    {
        return $this->status(503, $reasonPhrase);
    }

    public function status(
        int $code,
        string $reasonPhrase = '',
        ResponseInterface $response = null
    ): ResponseInterface
    {
        if (empty($response)) {
            $response = $this->getResponseFactory()->createResponse();
        }

        return $response->withStatus($code, $reasonPhrase);
    }

    public function json(
        JsonSerializable|array $data,
        int $statusCode = 200,
        ResponseInterface $response = null,
    ): ResponseInterface
    {
        if (empty($response)) {
            $response = $this->getResponseFactory()->createResponse();
        }

        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($data));

        return $response->withStatus($statusCode);
    }

    public function created(
        JsonSerializable|array $entity,
        ResponseInterface $response = null,
    ): ResponseInterface
    {
        return $this->json($entity, 201, $response);
    }

    public function movedToRoute(
        string $routeName,
        array $data = [],
        bool $isMovedPermanently = false,
        ResponseInterface $response = null
    ): ResponseInterface
    {
        return $this->moved(
            $this->getSitemap()->absoluteUrlFor($routeName, $data),
            $isMovedPermanently,
            $response
        );
    }

    public function moved(
        string $url,
        bool $isMovedPermanently = false,
        ResponseInterface $response = null
    ): ResponseInterface
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException(sprintf('URL "%s" is not valid.', $url));
        }

        if (empty($response)) {
            $response = $this->getResponseFactory()->createResponse();
        }

        return $response
            ->withStatus($isMovedPermanently ? 301 : 302)
            ->withHeader('Location', $url)
            ->withHeader('HX-Location', $url);
    }

    public function redirectToRoute(
        ServerRequestInterface $request,
        string $routeName,
        array $data = [],
        ResponseInterface $response = null,
    ): ResponseInterface
    {
        return $this->redirect(
            $request,
            $this->getSitemap()->absoluteUrlFor($routeName, $data),
            $response,
        );
    }

    public function redirect(
        ServerRequestInterface $request,
        string $url,
        ResponseInterface $response = null,
    ): ResponseInterface
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException(sprintf('URL "%s" is not valid.', $url));
        }

        if (empty($response)) {
            $response = $this->getResponseFactory()->createResponse();
        }

        if ($this->isHtmxRequest($request)) {
            return $response->withHeader('HX-Location', $url);
        }

        return $response
            ->withStatus(302)
            ->withHeader('Location', $url);
    }

    public function renderContent(
        string $content,
        int $statusCode = 200,
        string $reasonPhrase = null,
        ResponseInterface $response = null,
    ): ResponseInterface
    {
        $stream = new Stream('php://temp', 'wb+');
        $stream->write($content);
        $stream->rewind();

        if (empty($response)) {
            $response = $this->getResponseFactory()->createResponse();
        }

        return $response
            ->withStatus($statusCode, $reasonPhrase ?? '')
            ->withBody($stream);
    }

    public function renderTemplate(
        string $templatePath,
        array $templateVariables = [],
        int $statusCode = 200,
        string $reasonPhrase = null,
        ResponseInterface $response = null,
    ): ResponseInterface
    {
        return $this->renderContent(
            $this->get(TemplateEngineInterface::class)->fetch(
                $templatePath,
                $this->getTemplateVariablesProcessor()->process($templateVariables),
            ),
            $statusCode,
            $reasonPhrase,
            $response,
        );
    }

    protected function getRoute(ServerRequestInterface $request): RouteInterface
    {
        $route = $request->getAttribute($this->routeKey);

        if (!$route instanceof RouteInterface) {
            throw new RuntimeException('Failed to find route in request.');
        }

        return $route;
    }

    protected function isRoute(
        ServerRequestInterface $request,
        string $nodeName,
        string $requestMethod = null
    ): bool
    {
        $route = $this->getRoute($request);

        if ($requestMethod !== null && $request->getMethod() !== $requestMethod) {
            return false;
        }

        if ($route->getArgument(SitemapInterface::NODE_NAME_ROUTE_ARGUMENT) !== $nodeName) {
            return false;
        }

        return true;
    }

    protected function isTypeRoute(
        ServerRequestInterface $request,
        RoutingContextInterface $context,
        string $nodeName = 'index',
        string $requestMethod = null,
    ): bool
    {
        if ($requestMethod !== null && $request->getMethod() !== $requestMethod) {
            return false;
        }

        return sprintf(
            '%s_%s',
            $context->getRoutePrefix(),
            $nodeName
        ) === $this->getRoute($request)->getName();
    }

    protected function isMethod(ServerRequestInterface $request, string $requestMethod): bool
    {
        if ($request->getMethod() === 'POST' && $this->getPostMethodOverride()) {
            $parsedBody = $request->getParsedBody();

            if (is_array($parsedBody) && array_key_exists($this->getPostMethodOverride(), $parsedBody)) {
                return $parsedBody[$this->getPostMethodOverride()] === $requestMethod;
            }
        }

        return $request->getMethod() === $requestMethod;
    }

    protected function isHead(ServerRequestInterface $request): bool
    {
        return $this->isMethod($request, 'HEAD');
    }

    protected function isGet(ServerRequestInterface $request): bool
    {
        return $this->isMethod($request, 'GET');
    }

    protected function isPost(ServerRequestInterface $request): bool
    {
        return $this->isMethod($request, 'POST');
    }

    protected function isPut(ServerRequestInterface $request): bool
    {
        return $this->isMethod($request, 'PUT');
    }

    protected function isPatch(ServerRequestInterface $request): bool
    {
        return $this->isMethod($request, 'PATCH');
    }

    protected function isDelete(ServerRequestInterface $request): bool
    {
        return $this->isMethod($request, 'DELETE');
    }

    private string $posMethodOverride = NodeMiddlewareInterface::DEFAULT_POST_OVERRIDE_FIELD_NAME;

    protected function getPostMethodOverride(): ?string
    {
        return $this->posMethodOverride;
    }

    protected function setPostMethodOverride(?string $postMethodOverride): NodeMiddlewareInterface
    {
        $this->posMethodOverride = $postMethodOverride;

        return $this;
    }

    private ResponseFactoryInterface $responseFactory;

    protected function getResponseFactory(): ResponseFactoryInterface
    {
        if (empty($this->responseFactory)) {
            $this->responseFactory = new ResponseFactory();
        }

        return $this->responseFactory;
    }

    protected function getSitemap(): SitemapInterface
    {
        return $this->getContainer()->get(SitemapInterface::class);
    }

    protected function getIpAddress(ServerRequestInterface $request): ?string
    {
        return $request->getAttribute('ipAddress');
    }

    protected function isHtmxRequest(ServerRequestInterface $request): bool
    {
        return !empty($request->getHeader('HX-Request'));
    }

    /**
     * @template TClassName
     * @param  class-string<TClassName> $id
     * @return TClassName
     */
    public function get(string $id): mixed
    {
        return $this->getContainer()->get($id);
    }
}
