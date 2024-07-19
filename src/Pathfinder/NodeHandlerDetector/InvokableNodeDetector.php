<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\Pathfinder\NodeHandlerDetector;

use ActiveCollab\Sitemap\Location\SitemapLocationInterface;
use ActiveCollab\Sitemap\Handlers\HandlerInterface;
use ActiveCollab\Sitemap\Handlers\InvokableHandler\Invoker\InvokableHandlerInvoker;
use ActiveCollab\Sitemap\Nodes\File\FileInterface;
use ActiveCollab\Sitemap\Nodes\NodeInterface;
use Psr\Container\ContainerInterface;

class InvokableNodeDetector extends NodeHandlerDetector
{
    public function __construct(
        private ContainerInterface $container,
    )
    {
    }

    public function probe(NodeInterface $node): ?HandlerInterface
    {
        if ($node instanceof FileInterface && $this->hasPhpExtension($node)) {
            return new InvokableHandlerInvoker(
                $this->container,
                $this->container->get(SitemapLocationInterface::class),
                $node->getNodePath(),
            );
        }

        return null;
    }

    private function hasPhpExtension(FileInterface $fileNode): bool
    {
        return $fileNode->getExtension() === 'php';
    }
}
