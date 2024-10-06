<?php

/*
 * This file is part of the ActiveCollab Sitemap project.
 *
 * (c) A51 doo <info@activecollab.com>
 */

declare(strict_types=1);

namespace ActiveCollab\Sitemap\NodeMiddleware\Tester;

use GuzzleHttp\Psr7\Query;
use Laminas\Diactoros\ServerRequestFactory;
use ActiveCollab\Sitemap\NodeMiddleware\NodeMiddlewareInterface;
use ActiveCollab\Sitemap\NodeMiddleware\Tester\RequestHandler\NodeMiddlewareRequestHandler;
use ActiveCollab\Sitemap\NodeMiddleware\Tester\Result\NodeMiddlewareTestResult;
use ActiveCollab\Sitemap\NodeMiddleware\Tester\Result\NodeMiddlewareTestResultInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use RuntimeException;

class NodeMiddlewareTester implements NodeMiddlewareTesterInterface
{
    private TestCase $testCase;
    private ContainerInterface $container;

    public function __construct(TestCase $testCase, ContainerInterface $container)
    {
        $this->testCase = $testCase;
        $this->container = $container;
    }

    public function runTest(
        string $middlewareClass,
        array $middlewareConstructorArgs,
        string $uri,
        string $requestMethod,
        string $nodeName,
        array $attributes = null,
        $parsedBody = null
    ): NodeMiddlewareTestResultInterface
    {
        $request = (new ServerRequestFactory())->createServerRequest($requestMethod, $uri);

        if ($attributes !== null) {
            foreach ($attributes as $k => $v) {
                $request = $request->withAttribute($k, $v);
            }
        }

        if ($parsedBody !== null) {
            $request = $request->withParsedBody($parsedBody);
        }

        $parsedUri = parse_url($uri);

        if (!empty($parsedUri['query'])) {
            $request = $request->withQueryParams(Query::parse($parsedUri['query']));
        }

        $handler = new NodeMiddlewareRequestHandler();
        $response = $this->getMiddlewareMock(
            $this->testCase,
            $middlewareClass,
            $middlewareConstructorArgs,
            $requestMethod ?? 'GET',
            $nodeName
        )->process($request, $handler);

        return new NodeMiddlewareTestResult(
            $handler->getCapturedRequest() ?? $request,
            $response
        );
    }

    /**
     * @return MockObject|NodeMiddlewareInterface
     */
    private function getMiddlewareMock(
        TestCase $testCase,
        string $middlewareClass,
        array $middlewareConstructorArgs,
        string $filterRequestMethod,
        string $filterNodeName
    ): MockObject
    {
        /** @var MockObject|NodeMiddlewareInterface $middlewareMock */
        $middlewareMock = $testCase
            ->getMockBuilder($middlewareClass)
            ->setConstructorArgs($middlewareConstructorArgs)
            ->setMethods(
                [
                    'getContainer',
                    'isRoute',
                ]
            )
            ->getMock();

        $middlewareMock
            ->method('getContainer')
            ->willReturn($this->container);

        $middlewareMock
            ->method('isRoute')
            ->willReturnCallback(
                function () use ($filterNodeName, $filterRequestMethod) {
                    if (func_num_args() !== 3) {
                        throw new RuntimeException('Invalid number of arguments for isRoute method.');
                    }

                    return func_get_arg(1) === $filterNodeName
                        && (func_get_arg(2) === $filterRequestMethod
                            || ($filterRequestMethod === 'GET' && func_get_arg(2) === null)
                        );
                }
            );

        return $middlewareMock;
    }
}
