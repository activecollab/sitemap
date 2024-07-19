<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\Pathfinder\NodeHandlerDetector;

use ActiveCollab\TemplateEngine\TemplateEngineInterface;
use ActiveCollab\Sitemap\Handlers\HandlerInterface;
use ActiveCollab\Sitemap\Handlers\TemplateHandler\TemplateHandler;
use ActiveCollab\Sitemap\Nodes\File\FileInterface;
use ActiveCollab\Sitemap\Nodes\NodeInterface;

class TemplateNodeDetector extends NodeHandlerDetector
{
    private TemplateEngineInterface $templateEngine;
    private array $templateExtensions;

    public function __construct(
        TemplateEngineInterface $templateEngine,
        array $templateExtensions = [
            'twig',
            'tpl',
        ]
    )
    {
        $this->templateEngine = $templateEngine;
        $this->templateExtensions = $templateExtensions;
    }

    public function probe(NodeInterface $node): ?HandlerInterface
    {
        if ($node instanceof FileInterface && $this->hasTemplateExtension($node)) {
            return new TemplateHandler(
                $this->templateEngine,
                $node->getNodePath(),
            );
        }

        return null;
    }

    private function hasTemplateExtension(FileInterface $fileNode): bool
    {
        return in_array($fileNode->getExtension(), $this->templateExtensions);
    }
}
