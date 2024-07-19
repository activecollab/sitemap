<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\NodeMiddleware\TemplateVariablesProcessor;

class TemplateVariablesProcessor implements TemplateVariablesProcessorInterface
{
    public function process(array $templateVariables): array
    {
        return $templateVariables;
    }
}
