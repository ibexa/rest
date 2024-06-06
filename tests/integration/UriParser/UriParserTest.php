<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\Rest\UriParser;

use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Rest\UriParser\UriParserInterface;
use Ibexa\Contracts\Test\Core\IbexaKernelTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \Ibexa\Contracts\Rest\UriParser\UriParserInterface
 */
final class UriParserTest extends IbexaKernelTestCase
{
    private UriParserInterface $uriParser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->uriParser = $this->getIbexaTestCore()->getServiceByClassName(UriParserInterface::class);
    }

    /**
     * @return iterable<string, array{string, string, string, string}>
     */
    public static function getDataForTestGetAttributeFromUri(): iterable
    {
        yield 'Get sectionId attribute from ibexa.rest.load_section GET route' => [
            'GET',
            '/api/ibexa/v2/content/sections/2',
            'sectionId',
            '2',
        ];

        yield 'Get userId attribute from ibexa.rest.assign_user_to_user_group POST route' => [
            'POST',
            '/api/ibexa/v2/user/users/14/groups',
            'userId',
            '14',
        ];
    }

    /**
     * @dataProvider getDataForTestGetAttributeFromUri
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function testGetAttributeFromUri(
        string $method,
        string $uri,
        string $attributeName,
        string $expectedValue
    ): void {
        self::assertSame($expectedValue, $this->uriParser->getAttributeFromUri($uri, $attributeName, $method));
    }

    /**
     * @return iterable<string, array{string, string, string, string}>
     */
    public static function getDataForTestGetAttributeFromUriThrowsException(): iterable
    {
        $uri = '/api/ibexa/v2/content/sections/1';
        yield 'Invalid attribute' => [
            'GET',
            $uri,
            'session',
            "No attribute 'session' in route matched from $uri",
        ];

        yield 'Invalid method' => [
            'POST',
            $uri,
            'sectionId',
            "Method 'POST' is not allowed for '$uri'. Allowed: [GET, PATCH, DELETE]",
        ];

        yield 'Invalid route' => [
            'GET',
            '/api/ibexa/v2/foo-bar-baz',
            'foo',
            'No routes found for "/api/ibexa/v2/foo-bar-baz/"',
        ];

        yield 'Non-REST route' => [
            'GET',
            '/admin',
            'foo',
            // The real exception message got covered by this one due to BC for the original Router-based Request Parser
            'No route matched \'/admin\'',
        ];
    }

    /**
     * @dataProvider getDataForTestGetAttributeFromUriThrowsException
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function testGetAttributeFromUriThrowsException(
        string $method,
        string $uri,
        string $attributeName,
        string $expectedExceptionMessage
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);
        $this->uriParser->getAttributeFromUri($uri, $attributeName, $method);
    }

    /**
     * @return iterable<string, array{\Symfony\Component\HttpFoundation\Request, bool}>
     */
    public static function getDataForTestIsRestRequest(): iterable
    {
        yield ($uri = '/api/ibexa/v2/foo') => [
            Request::create($uri),
            true,
        ];

        yield ($uri = '/api/acme/v1.5/bar') => [
            Request::create($uri),
            true,
        ];

        yield ($uri = '/baz') => [
            Request::create($uri),
            false,
        ];
    }

    /**
     * @dataProvider getDataForTestIsRestRequest
     */
    public function testIsRestRequest(Request $request, bool $isRestRequest): void
    {
        self::assertSame($isRestRequest, $this->uriParser->isRestRequest($request));
    }

    /**
     * @dataProvider getDataForTestIsRestRequest
     */
    public function testHasRestPrefix(Request $request, bool $hasRestPrefix): void
    {
        self::assertSame($hasRestPrefix, $this->uriParser->hasRestPrefix($request->getPathInfo()));
    }

    /**
     * @return iterable<string, array{string, string, array<string, string>}>
     */
    public static function getDataForTestMatchUri(): iterable
    {
        yield ($uri = '/api/ibexa/v2/content/objectstategroups/123/objectstates/456') => [
            $uri,
            'PATCH',
            [
                '_route' => 'ibexa.rest.update_object_state',
                '_controller' => 'Ibexa\Rest\Server\Controller\ObjectState:updateObjectState',
                'objectStateGroupId' => '123',
                'objectStateId' => '456',
            ],
        ];
    }

    /**
     * @dataProvider getDataForTestMatchUri
     *
     * @param array<string, string> $expectedMatch
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\InvalidArgumentException
     */
    public function testMatchUri(string $uri, string $method, array $expectedMatch): void
    {
        $actualMatch = $this->uriParser->matchUri($uri, $method);
        foreach ($expectedMatch as $expectedKey => $expectedValue) {
            self::assertArrayHasKey($expectedKey, $actualMatch);
            self::assertSame($expectedValue, $actualMatch[$expectedKey]);
        }
    }

    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function getInvalidDataForTestMatchUri(): iterable
    {
        // unknown route
        yield ($uri = '/api/ibexa/v2/foo/123') => [
            $uri,
            'GET',
            "No routes found for \"$uri/\"",
        ];

        // the route exists only for POST method
        yield ($uri = '/user/sessions/MySessionID/refresh') => [
            $uri,
            'GET',
            'No route matched \'/user/sessions/MySessionID/refresh\'',
        ];
    }

    /**
     * @dataProvider getInvalidDataForTestMatchUri
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\InvalidArgumentException
     */
    public function testMatchUriThrowsException(string $uri, string $method, string $expectedExceptionMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);
        $this->uriParser->matchUri($uri, $method);
    }
}
