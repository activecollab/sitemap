<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\Handlers\InvokableHandler;

use ActiveCollab\ContainerAccess\ContainerAccessInterface\Implementation as ContainerAccessImplementation;
use ActiveCollab\Sitemap\Handlers\Handler;
use ActiveCollab\Sitemap\RequestHandler\HttpRequestHandlerTrait;
use ActiveCollab\Sitemap\Sitemap\SitemapInterface;
use ActiveCollab\TemplateEngine\TemplateEngineInterface;
use Laminas\Diactoros\ResponseFactory;
use Psr\Http\Message\ResponseFactoryInterface;

abstract class InvokableHandler extends Handler implements InvokableHandlerInterface
{
    use HttpRequestHandlerTrait;
    use ContainerAccessImplementation;

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

    protected function getTemplateEngine(): TemplateEngineInterface
    {
        return $this->get(TemplateEngineInterface::class);
    }

    /**
     * @template TClassName
     * @param  class-string<TClassName> $id
     * @return TClassName
     */
    protected function get(string $id): mixed
    {
        return $this->getContainer()->get($id);
    }
}
