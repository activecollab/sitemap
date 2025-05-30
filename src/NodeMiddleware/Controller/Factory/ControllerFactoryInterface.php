<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\NodeMiddleware\Controller\Factory;

use ActiveCollab\Sitemap\Handlers\HandlerInterface;
use ActiveCollab\Sitemap\NodeMiddleware\Controller\ControllerInterface;

interface ControllerFactoryInterface
{
    public function createController(
        string $controllerType,
        string $absoluteMiddlewarePath,
    ): ControllerInterface;

    public function createHandler(string $handlerType): HandlerInterface;
}
