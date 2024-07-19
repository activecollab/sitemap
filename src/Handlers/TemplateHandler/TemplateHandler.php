<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\Handlers\TemplateHandler;

use ActiveCollab\TemplateEngine\TemplateEngineInterface;
use ActiveCollab\Sitemap\Handlers\Handler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TemplateHandler extends Handler implements TemplateHandlerInterface
{
    private TemplateEngineInterface $templateEngine;
    private string $templateName;
    private array $templateAttributes;

    public function __construct(
        TemplateEngineInterface $templateEngine,
        string $templateName,
        array $templateAttributes = []
    )
    {
        $this->templateEngine = $templateEngine;
        $this->templateAttributes = $templateAttributes;
        $this->templateName = $templateName;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
    ): ResponseInterface
    {
        foreach (['formData', 'serviceProcessingResult'] as $requiredTemplateVariables) {
            if (!$request->getAttribute($requiredTemplateVariables)) {
                $request = $request
                    ->withAttribute($requiredTemplateVariables, null);
            }
        }

        $response->getBody()->write(
            $this->templateEngine->fetch(
                $this->templateName,
                array_merge(
                    $this->templateAttributes,
                    $request->getAttributes(),
                )
            )
        );

        return $response;
    }
}
