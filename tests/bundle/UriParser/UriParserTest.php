<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\Rest\UriParser;

use Ibexa\Bundle\Rest\UriParser\UriParser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;

/**
 * @covers \Ibexa\Bundle\Rest\UriParser\UriParser
 */
final class UriParserTest extends TestCase
{
    private const REST_PATH_PREFIX = '/api/ibexa/v2';

    private const CUSTOM_REST_URI = '/api/acme/v1/foo';

    /**
     * @return iterable<string, array{string, bool}>
     */
    public static function getDataForTestHasRestPrefix(): iterable
    {
        // REST URIs
        yield ($uri = '/api/ibexa/v2/true') => [$uri, true];
        yield ($uri = '/api/bundle-name/v2/true') => [$uri, true];
        yield ($uri = '/api/MyBundle12/v2/true') => [$uri, true];
        yield ($uri = '/api/ThisIs_Bundle123/v2/true') => [$uri, true];
        yield ($uri = '/api/my-bundle/v1/true') => [$uri, true];
        yield ($uri = '/api/my-bundle/v2/true') => [$uri, true];
        yield ($uri = '/api/my-bundle/v2.7/true') => [$uri, true];
        yield ($uri = '/api/my-bundle/v122.73/true') => [$uri, true];

        // non-REST URIs
        yield ($uri = '/non-rest-route') => [$uri, false];
        yield ($uri = '/ap/ezp/v2/false') => [$uri, false];
        yield ($uri = '/api/bundle name/v2/false') => [$uri, false];
        yield ($uri = '/api/My/Bundle/v2/false') => [$uri, false];
        yield ($uri = '/api//v2/false') => [$uri, false];
        yield ($uri = '/api/my-bundle/v/false') => [$uri, false];
        yield ($uri = '/api/my-bundle/v2-2/false') => [$uri, false];
        yield ($uri = '/api/my-bundle/v2 7/false') => [$uri, false];
        yield ($uri = '/api/my-bundle/v/7/false') => [$uri, false];
    }

    /**
     * @dataProvider getDataForTestHasRestPrefix
     */
    public function testHasRestPrefix(string $uri, bool $expectedHasRestPrefix): void
    {
        $uriParser = new UriParser($this->createStub(UrlMatcherInterface::class));

        self::assertSame($expectedHasRestPrefix, $uriParser->hasRestPrefix($uri));
    }

    /**
     * @return iterable<string, array{string, bool}>
     */
    public static function getDataForTestIsRestRequestWithoutMatchingRoutes(): iterable
    {
        yield 'non-REST URI' => ['/non-rest-route', false];
        yield 'unknown URI under the REST path prefix' => [self::REST_PATH_PREFIX . '/unknown', true];
    }

    /**
     * @dataProvider getDataForTestIsRestRequestWithoutMatchingRoutes
     */
    public function testIsRestRequestWithoutMatchingRoutes(string $uri, bool $expectedIsRestRequest): void
    {
        $restRouteMatcher = $this->createMock(UrlMatcherInterface::class);
        $restRouteMatcher->expects(self::never())->method('match');

        $uriParser = $this->createUriParser($restRouteMatcher);

        self::assertSame($expectedIsRestRequest, $uriParser->isRestRequest(Request::create($uri)));
    }

    public function testIsRestRequestWhenRouteExists(): void
    {
        $restRouteMatcher = $this->createRestRouteMatcherMock();
        $restRouteMatcher
            ->expects(self::once())
            ->method('match')
            ->with(self::CUSTOM_REST_URI)
            ->willReturn(['_route' => 'acme.rest.load_foo']);

        $uriParser = $this->createUriParser($restRouteMatcher);

        self::assertTrue($uriParser->isRestRequest(Request::create(self::CUSTOM_REST_URI)));
    }

    /**
     * @return iterable<string, array{\Throwable, bool}>
     */
    public static function getDataForTestIsRestRequestWhenRouteDoesNotMatch(): iterable
    {
        yield 'route exists for another HTTP method' => [new MethodNotAllowedException(['POST']), true];
        yield 'route does not exist' => [new ResourceNotFoundException('No routes found'), false];
    }

    /**
     * @dataProvider getDataForTestIsRestRequestWhenRouteDoesNotMatch
     */
    public function testIsRestRequestWhenRouteDoesNotMatch(
        \Throwable $matchException,
        bool $expectedIsRestRequest
    ): void {
        $restRouteMatcher = $this->createRestRouteMatcherMock();
        $restRouteMatcher
            ->expects(self::once())
            ->method('match')
            ->with(self::CUSTOM_REST_URI)
            ->willThrowException($matchException);

        $uriParser = $this->createUriParser($restRouteMatcher);

        self::assertSame($expectedIsRestRequest, $uriParser->isRestRequest(Request::create(self::CUSTOM_REST_URI)));
    }

    public function testIsRestRequestMatchesRouteInRequestContext(): void
    {
        $originalContext = new RequestContext();
        $usedContexts = [];

        $restRouteMatcher = $this->createMock(UrlMatcherInterface::class);
        $restRouteMatcher->method('getContext')->willReturn($originalContext);
        $restRouteMatcher
            ->expects(self::exactly(2))
            ->method('setContext')
            ->willReturnCallback(
                static function (RequestContext $context) use (&$usedContexts): void {
                    $usedContexts[] = $context;
                }
            );
        $restRouteMatcher->method('match')->willReturn(['_route' => 'acme.rest.load_foo']);

        $uriParser = $this->createUriParser($restRouteMatcher);
        $uriParser->isRestRequest(Request::create('https://example.com' . self::CUSTOM_REST_URI, 'PUT'));

        self::assertCount(2, $usedContexts);
        self::assertSame('PUT', $usedContexts[0]->getMethod());
        self::assertSame('example.com', $usedContexts[0]->getHost());
        self::assertSame($originalContext, $usedContexts[1], 'The original matcher context should be restored');
    }

    public function testIsRestRequestFallsBackToUrlMatcher(): void
    {
        $urlMatcher = $this->createRestRouteMatcherMock();
        $urlMatcher
            ->expects(self::once())
            ->method('match')
            ->with(self::CUSTOM_REST_URI)
            ->willThrowException(new ResourceNotFoundException('No routes found'));

        // no dedicated route matcher and no REST path prefixes configured
        $uriParser = new UriParser($urlMatcher);

        self::assertFalse($uriParser->isRestRequest(Request::create(self::CUSTOM_REST_URI)));
    }

    private function createUriParser(UrlMatcherInterface $restRouteMatcher): UriParser
    {
        // the general-purpose matcher is reserved for matchUri(), it must not be used to check whether a route exists
        $urlMatcher = $this->createMock(UrlMatcherInterface::class);
        $urlMatcher->expects(self::never())->method('match');

        return new UriParser(
            $urlMatcher,
            UriParser::DEFAULT_REST_PREFIX_PATTERN,
            $restRouteMatcher,
            [self::REST_PATH_PREFIX]
        );
    }

    /**
     * @return \Symfony\Component\Routing\Matcher\UrlMatcherInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createRestRouteMatcherMock(): MockObject
    {
        $restRouteMatcher = $this->createMock(UrlMatcherInterface::class);
        $restRouteMatcher->method('getContext')->willReturn(new RequestContext());

        return $restRouteMatcher;
    }
}
