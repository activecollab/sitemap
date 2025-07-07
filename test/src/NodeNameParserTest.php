<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\Test;

use ActiveCollab\Sitemap\Nodes\NodeNameParser\NodeNameParser;
use PHPUnit\Framework\TestCase;

class NodeNameParserTest extends TestCase
{
    /**
     * @dataProvider nodeNameDataProvider
     */
    public function testNodeNameParser(
        string $basename,
        string $expectedNodeName,
        string $expectedExtension,
        bool $expectedIsHidden,
        bool $expectedIsVariable,
        bool $expectedIsSystem,
    ): void
    {
        $node = new NodeNameParser($basename);

        $this->assertSame($expectedNodeName, $node->getNodeName());
        $this->assertSame($expectedExtension, $node->getExtension());
        $this->assertSame($expectedIsHidden, $node->isHidden());
        $this->assertSame($expectedIsSystem, $node->isSystem());
        $this->assertSame($expectedIsVariable, $node->isVariable());
    }

    public static function nodeNameDataProvider(): array
    {
        return [

            // Hidden file.
            [
                '.gitignore',
                'gitignore',
                '',
                true,
                false,
                false,
            ],

            // Regular node.
            [
                'delete.php',
                'delete',
                'php',
                false,
                false,
                false,
            ],

            // Variable node.
            [
                '__document_slug__',
                'document_slug',
                '',
                false,
                true,
                false,
            ],

            // Middleware.
            [
                '__middleware.php',
                'middleware',
                'php',
                false,
                false,
                true,
            ],
        ];
    }
}
