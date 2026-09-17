<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\Rest\Functional;

use Ibexa\Tests\Bundle\Rest\Functional\TestCase as RESTFunctionalTestCase;
use PHPUnit\Framework\Attributes\Depends;
use Symfony\Component\HttpFoundation\Response;

class BookmarkTest extends RESTFunctionalTestCase
{
    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function testCreateBookmark(): int
    {
        $content = $this->createFolder(__FUNCTION__, '/api/ibexa/v2/content/locations/1/2');
        $contentLocations = $this->getContentLocations($content['_href']);

        $locationPathParts = explode('/', $contentLocations['LocationList']['Location'][0]['_href']);
        $locationId = (int) array_pop($locationPathParts);

        $request = $this->createHttpRequest(
            'POST',
            '/api/ibexa/v2/bookmark/' . $locationId
        );

        $response = $this->sendHttpRequest($request);

        $this->assertHttpResponseCodeEquals($response, Response::HTTP_CREATED);

        return $locationId;
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[Depends('testCreateBookmark')]
    public function testCreateBookmarkIfAlreadyExists(int $locationId): void
    {
        $request = $this->createHttpRequest(
            'POST',
            '/api/ibexa/v2/bookmark/' . $locationId
        );

        $response = $this->sendHttpRequest($request);

        $this->assertHttpResponseCodeEquals($response, Response::HTTP_CONFLICT);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[Depends('testCreateBookmark')]
    public function testIsBookmarked(int $locationId): void
    {
        $request = $this->createHttpRequest(
            'HEAD',
            '/api/ibexa/v2/bookmark/' . $locationId
        );

        $response = $this->sendHttpRequest($request);

        $this->assertHttpResponseCodeEquals($response, Response::HTTP_OK);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[Depends('testDeleteBookmark')]
    public function testIsBookmarkedReturnsNotFound(int $locationId): void
    {
        $request = $this->createHttpRequest(
            'HEAD',
            '/api/ibexa/v2/bookmark/' . $locationId
        );

        $response = $this->sendHttpRequest($request);

        $this->assertHttpResponseCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[Depends('testCreateBookmark')]
    public function testDeleteBookmark(int $locationId): int
    {
        $request = $this->createHttpRequest(
            'DELETE',
            '/api/ibexa/v2/bookmark/' . $locationId
        );

        $response = $this->sendHttpRequest($request);

        $this->assertHttpResponseCodeEquals($response, Response::HTTP_NO_CONTENT);

        return $locationId;
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function testLoadBookmarks(): void
    {
        $request = $this->createHttpRequest(
            'GET',
            '/api/ibexa/v2/bookmark?offset=1&limit=100',
            'BookmarkList+xml',
            'BookmarkList+xml'
        );

        $response = $this->sendHttpRequest($request);

        $this->assertHttpResponseCodeEquals($response, Response::HTTP_OK);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    #[Depends('testDeleteBookmark')]
    public function testDeleteBookmarkReturnNotFound(int $locationId): void
    {
        $request = $this->createHttpRequest(
            'DELETE',
            '/api/ibexa/v2/bookmark/' . $locationId
        );

        $response = $this->sendHttpRequest($request);

        $this->assertHttpResponseCodeEquals($response, Response::HTTP_NOT_FOUND);
    }
}
