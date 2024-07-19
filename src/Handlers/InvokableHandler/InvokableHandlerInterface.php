<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\Handlers\InvokableHandler;

use ActiveCollab\ContainerAccess\ContainerAccessInterface;
use ActiveCollab\Sitemap\Handlers\HandlerInterface;

interface InvokableHandlerInterface extends HandlerInterface, ContainerAccessInterface
{
}
