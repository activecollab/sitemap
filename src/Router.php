<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap;

use DirectoryIterator;
use ActiveCollab\Sitemap\Nodes\Directory\Directory;
use ActiveCollab\Sitemap\Nodes\Directory\DirectoryInterface;
use ActiveCollab\Sitemap\Nodes\File\File;
use RuntimeException;

class Router implements RouterInterface
{
    public function scan(string $routingRoot): DirectoryInterface
    {
        $routingRoot = rtrim($routingRoot, '/');

        if (!is_dir($routingRoot)) {
            throw new RuntimeException(sprintf('Path "%s" is not a directory.', $routingRoot));
        }

        return $this->scanDir($routingRoot, '');
    }

    private function scanDir(string $routingRoot, string $dirPath): DirectoryInterface
    {
        $result = new Directory($routingRoot, $dirPath);

        foreach (new DirectoryIterator(sprintf('%s/%s', $routingRoot, $dirPath)) as $entity) {
            if ($entity->isDot() || $this->isHiddenFile($entity->getFilename()) || $entity->isLink()) {
                continue;
            }

            $nodePath = $this->getNodePath($routingRoot, $entity->getPathname());

            if ($entity->isFile()) {
                $result->addFiles(new File($routingRoot, $nodePath));
            } elseif ($entity->isDir()) {
                $result->addSubdirectory($this->scanDir($routingRoot, $nodePath));
            }
        }

        return $result;
    }

    private function isHiddenFile(string $filename): bool
    {
        return mb_substr($filename, 0, 1) === '.';
    }

    private function getNodePath(string $routingRoot, string $nodePath): string
    {
        return mb_substr($nodePath, mb_strlen($routingRoot) + 1);
    }
}
