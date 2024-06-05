<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\Rest\RequestParser;

use Ibexa\Bundle\Rest\RequestParser\Router as RouterRequestParser;
use Ibexa\Bundle\Rest\UriParser\UriParser;
use Ibexa\Contracts\Rest\Exceptions\InvalidArgumentException;
use Ibexa\Rest\RequestParser;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\Rule\InvokedCount as InvokedCountMatcher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

final class RouterTest extends TestCase
{
    private RouterInterface $router;

    private static $routePrefix = '/api/test/v1';

    public function testParse(): void
    {
        $uri = self::$routePrefix . '/';

        $expectedMatchResult = [
            '_route' => 'ibexa.rest.test_route',
            '_controller' => '',
        ];

        $this->getRouterInvocationMockerForMatchingUri($uri)
             ->willReturn($expectedMatchResult)
        ;

        self::assertEquals(
            $expectedMatchResult,
            $this->getRequestParser()->parse($uri)
        );
    }

    public function testParseNoMatch(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $exceptionMessage = 'No route matched \'/api/test/v1/nomatch\'';
        $this->expectExceptionMessage($exceptionMessage);

        $uri = self::$routePrefix . '/nomatch';

        $this->getRouterInvocationMockerForMatchingUri($uri)
             ->willThrowException(new ResourceNotFoundException($exceptionMessage))
        ;

        $this->getRequestParser()->parse($uri);
    }

    public function testParseNoPrefix(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $exceptionMessage = 'No route matched \'/no/prefix\'';
        $this->expectExceptionMessage($exceptionMessage);

        $uri = '/no/prefix';

        // invalid prefix should cause internal url matcher not to be called
        $this->getRouterInvocationMockerForMatchingUri($uri, self::never());

        $this->getRequestParser()->parse($uri);
    }

    public function testParseHref(): void
    {
        $href = '/api/test/v1/content/objects/1';

        $expectedMatchResult = [
            '_route' => 'ibexa.rest.test_parse_href',
            'contentId' => 1,
        ];

        $this->getRouterInvocationMockerForMatchingUri($href)
             ->willReturn($expectedMatchResult)
        ;

        self::assertEquals(1, $this->getRequestParser()->parseHref($href, 'contentId'));
    }

    public function testParseHrefAttributeNotFound(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'No attribute \'badAttribute\' in route matched from /api/test/v1/content/no-attribute'
        );

        $href = '/api/test/v1/content/no-attribute';

        $matchResult = [
            '_route' => 'ibexa.rest.test_parse_href_attribute_not_found',
        ];

        $this->getRouterInvocationMockerForMatchingUri($href)
             ->willReturn($matchResult)
        ;

        self::assertEquals(1, $this->getRequestParser()->parseHref($href, 'badAttribute'));
    }

    public function testGenerate(): void
    {
        $routeName = 'ibexa.rest.test_generate';
        $arguments = ['arg1' => 1];

        $expectedResult = self::$routePrefix . '/generate/' . $arguments['arg1'];
        $this->getRouterMock()
             ->expects(self::once())
             ->method('generate')
             ->with($routeName, $arguments)
             ->willReturn($expectedResult)
        ;

        self::assertEquals(
            $expectedResult,
            $this->getRequestParser()->generate($routeName, $arguments)
        );
    }

    private function getRequestParser(): RequestParser
    {
        $routerMock = $this->getRouterMock();

        return new RouterRequestParser(
            $routerMock,
            new UriParser($routerMock)
        );
    }

    /**
     * @return \Symfony\Component\Routing\RouterInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private function getRouterMock(): RouterInterface
    {
        if (!isset($this->router)) {
            $this->router = $this->createMock(RouterInterface::class);

            $this->router
                ->method('getContext')
                ->willReturn(new RequestContext())
            ;
        }

        return $this->router;
    }

    private function getRouterInvocationMockerForMatchingUri(
        string $uri,
        ?InvokedCountMatcher $invokedCount = null
    ): InvocationMocker {
        return $this->getRouterMock()
            ->expects($invokedCount ?? self::once())
            ->method('match')
            ->with($uri)
        ;
    }
}
