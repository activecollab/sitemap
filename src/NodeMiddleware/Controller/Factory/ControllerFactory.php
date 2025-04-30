<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\NodeMiddleware\Controller\Factory;

use ActiveCollab\ContainerAccess\ContainerAccessInterface;
use ActiveCollab\Sitemap\Handlers\HandlerInterface;
use ActiveCollab\Sitemap\NodeMiddleware\Controller\ControllerInterface;
use LogicException;
use Psr\Container\ContainerInterface;

class ControllerFactory implements ControllerFactoryInterface
{
    public function __construct(
        private ContainerInterface $container,
    )
    {
    }

    public function createController(
        string $controllerType,
        string $absoluteMiddlewarePath,
    ): ControllerInterface
    {
        return $this->container->make(
            $controllerType,
            [
                'absoluteMiddlewarePath' => $absoluteMiddlewarePath,
            ],
        );
    }

    public function createHandler(string $handlerType): HandlerInterface
    {
        $handler = $this->container->make($handlerType);

        if (!$handler instanceof HandlerInterface) {
            throw new LogicException('Handler must implement HandlerInterface');
        }

        if ($handler instanceof ContainerAccessInterface) {
            $handler->setContainer($this->container);
        }

        return $handler;
    }
}
