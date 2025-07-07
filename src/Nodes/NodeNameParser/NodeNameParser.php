<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\Nodes\NodeNameParser;

class NodeNameParser implements NodeNameParserInterface
{
    private string $node_name = '';
    private string $extension = '';
    private bool $is_hidden = false;
    private bool $is_system = false;
    private bool $is_variable = false;

    public function __construct(string $basename)
    {
        $bits = explode('.', $basename);

        if (empty($bits[0])) {
            $this->is_hidden = true;

            unset($bits[0]);
            $bits = array_values($bits);
        }

        if (count($bits) > 1) {
            $this->extension = $bits[count($bits) - 1];
            unset($bits[count($bits) - 1]);
        }

        $clean_node_name = implode('.', $bits);

        if (mb_substr($clean_node_name, 0, 2) === '__') {
            if (mb_substr($clean_node_name, -2) === '__') {
                $this->node_name = mb_substr($clean_node_name, 2, mb_strlen($clean_node_name) - 4);
                $this->is_variable = true;
            } else {
                $this->node_name = mb_substr($clean_node_name, 2);
                $this->is_system = true;
            }
        } else {
            $this->node_name = $clean_node_name;
        }
    }

    public function getFileProperties(): array
    {
        return [
            $this->node_name,
            $this->extension,
            $this->is_hidden,
            $this->is_system,
            $this->is_variable,
        ];
    }

    public function getDirectoryProperties(): array
    {
        return [
            $this->extension
                ? $this->node_name . '.' . $this->extension
                : $this->node_name,
            $this->is_hidden,
            $this->is_system,
            $this->is_variable,
        ];
    }

    public function getNodeName(): string
    {
        return $this->node_name;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function isHidden(): bool
    {
        return $this->is_hidden;
    }

    public function isSystem(): bool
    {
        return $this->is_system;
    }

    public function isVariable(): bool
    {
        return $this->is_variable;
    }
}
