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

abstract class InvokableHandler extends Handler implements InvokableHandlerInterface
{
    use ContainerAccessImplementation;

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
