<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\Nodes\Directory;

use ActiveCollab\Sitemap\Nodes\File\FileInterface;
use ActiveCollab\Sitemap\Nodes\NodeInterface;

interface DirectoryInterface extends NodeInterface
{
    public function isEmpty(): bool;

    public function hasIndex(): bool;
    public function getIndex(): ?FileInterface;

    public function hasMiddleware(): bool;
    public function getMiddleware(): ?FileInterface;

    public function addSubdirectory(DirectoryInterface ...$directories): void;

    /**
     * @return DirectoryInterface[]
     */
    public function getSubdirectories(): array;
    public function getSubdirectory(string $name): ?DirectoryInterface;
    public function addFiles(FileInterface ...$files): void;

    /**
     * @return FileInterface[]
     */
    public function getFiles(): array;
}
