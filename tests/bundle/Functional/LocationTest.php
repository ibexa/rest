<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Bundle\Rest\Functional;

use Ibexa\Tests\Bundle\Rest\Functional\TestCase as RESTFunctionalTestCase;

class LocationTest extends RESTFunctionalTestCase
{
    /**
     * Covers POST /content/objects/{contentId}/locations.
     *
     * @returns string location href
     */
    public function testCreateLocation()
    {
        $content = $this->createFolder('testCreateLocation', '/api/ibexa/v2/content/locations/1/2');
        $contentHref = $content['_href'];

        $remoteId = $this->addTestSuffix('testCreateLocation');

        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<LocationCreate>
  <ParentLocation href="/api/ibexa/v2/content/locations/1/43" />
  <remoteId>{$remoteId}</remoteId>
  <priority>0</priority>
  <hidden>false</hidden>
  <sortField>PATH</sortField>
  <sortOrder>ASC</sortOrder>
</LocationCreate>
XML;
        $request = $this->createHttpRequest(
            'POST',
            "$contentHref/locations",
            'LocationCreate+xml',
            'Location+json',
            $body
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        return $response->getHeader('Location')[0];
    }

    /**
     * @depends testCreateLocation
     * Covers GET /content/locations?remoteId=<locationRemoteId>
     */
    public function testRedirectLocationByRemoteId($locationHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ibexa/v2/content/locations?remoteId=' . $this->addTestSuffix('testCreateLocation'))
        );

        self::assertHttpResponseCodeEquals($response, 307);
        self::assertHttpResponseHasHeader($response, 'Location', $locationHref);
    }

    /**
     * @depends testCreateLocation
     * Covers GET /content/locations?id=<locationId>
     */
    public function testRedirectLocationById($locationHref): void
    {
        $hrefParts = explode('/', $locationHref);
        $id = array_pop($hrefParts);
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "/api/ibexa/v2/content/locations?id=$id")
        );

        self::assertHttpResponseCodeEquals($response, 307);
        self::assertHttpResponseHasHeader($response, 'Location', $locationHref);
    }

    /**
     * @depends testCreateLocation
     * Covers GET /content/locations?urlAlias=<Path/To-Content>
     */
    public function testRedirectLocationByURLAlias($locationHref): void
    {
        $testUrlAlias = 'firstPart/secondPart/testUrlAlias';
        $this->createUrlAlias($locationHref, $testUrlAlias);

        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "/api/ibexa/v2/content/locations?urlAlias={$testUrlAlias}")
        );

        self::assertHttpResponseCodeEquals($response, 307);
        self::assertHttpResponseHasHeader($response, 'Location', $locationHref);
    }

    /**
     * @depends testCreateLocation
     * Covers GET /content/locations/{locationPath}
     */
    public function testLoadLocation(string $locationHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $locationHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateLocation
     * Covers COPY /content/locations/{locationPath}
     *
     * @return string the created location's href
     */
    public function testCopySubtree(string $locationHref)
    {
        $request = $this->createHttpRequest(
            'COPY',
            $locationHref,
            '',
            '',
            '',
            ['Destination' => '/api/ibexa/v2/content/locations/1/43']
        );
        $response = $this->sendHttpRequest($request);
        $this->addCreatedElement($response->getHeaderLine('Location'));

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        return $response->getHeader('Location')[0];
    }

    /**
     * Covers MOVE /content/locations/{locationPath}.
     *
     * @depends testCopySubtree
     */
    public function testMoveSubtree(string $locationHref): string
    {
        $request = $this->createHttpRequest(
            'MOVE',
            $locationHref,
            '',
            '',
            '',
            ['Destination' => '/api/ibexa/v2/content/locations/1/5']
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        return $locationHref;
    }

    /**
     * @depends testCreateLocation
     * Covers GET /content/objects/{contentId}/locations
     */
    public function testLoadLocationsForContent($contentHref): void
    {
    }

    /**
     * @depends testCreateLocation
     * Covers SWAP /content/locations/{locationPath}
     */
    public function testSwapLocation($locationHref): void
    {
        self::markTestSkipped('@todo Implement');

        /*$content = $this->createFolder( __FUNCTION__, "/api/ibexa/v2/content/locations/1/2" );

        $request = $this->createHttpRequest( 'SWAP', $locationHref );
        $request->addHeader( "Destination: $newFolderHref" );

        $response = $this->sendHttpRequest( $request );
        self::assertHttpResponseCodeEquals( $response, 204 );*/
    }

    /**
     * @depends testCreateLocation
     * Covers GET /content/locations/{locationPath}/children
     */
    public function testLoadLocationChildren($locationHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "$locationHref/children", '', 'LocationList+json')
        );

        self::assertHttpResponseCodeEquals($response, 200);
        self::assertHttpResponseHasHeader($response, 'Content-Type', $this->generateMediaTypeString('LocationList+json'));
    }

    /**
     * Covers PATCH /content/locations/{locationPath}.
     *
     * @depends testCreateLocation
     */
    public function testUpdateLocation(string $locationHref): void
    {
        $body = <<< XML
<LocationUpdate>
  <priority>3</priority>
  <sortField>PATH</sortField>
  <sortOrder>ASC</sortOrder>
</LocationUpdate>
XML;

        $request = $this->createHttpRequest(
            'PATCH',
            $locationHref,
            'LocationUpdate+xml',
            'Location+json',
            $body
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateLocation
     * Covers DELETE /content/locations/{path}
     */
    public function testDeleteSubtree(string $locationHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $locationHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }

    private function createUrlAlias(string $locationHref, string $urlAlias): string
    {
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<UrlAliasCreate type="LOCATION">
  <location href="{$locationHref}" />
  <path>/{$urlAlias}</path>
  <languageCode>eng-GB</languageCode>
  <alwaysAvailable>false</alwaysAvailable>
  <forward>true</forward>
</UrlAliasCreate>
XML;

        $request = $this->createHttpRequest(
            'POST',
            '/api/ibexa/v2/content/urlaliases',
            'UrlAliasCreate+xml',
            'UrlAlias+json',
            $xml
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseHasHeader($response, 'Location');
        $href = $response->getHeader('Location')[0];
        $this->addCreatedElement($href);

        return $href;
    }

    /**
     * @depends testMoveSubtree
     */
    public function testMoveLocation(string $locationHref): string
    {
        $request = $this->createHttpRequest(
            'POST',
            $locationHref,
            'MoveLocationInput+json',
            '',
            json_encode(['MoveLocationInput' => ['destination' => '/1/2']], JSON_THROW_ON_ERROR),
        );

        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        return $locationHref;
    }

    /**
     * @depends testMoveLocation
     */
    public function testSwap(string $locationHref): void
    {
        $request = $this->createHttpRequest(
            'COPY',
            $locationHref,
            '',
            '',
            '',
            ['Destination' => '/api/ibexa/v2/content/locations/1/43']
        );
        $response = $this->sendHttpRequest($request);
        $newCopiedLocation = $response->getHeader('Location')[0];

        $request = $this->createHttpRequest(
            'COPY',
            $locationHref,
            '',
            '',
            '',
            ['Destination' => '/api/ibexa/v2/content/locations/1/43']
        );
        $response = $this->sendHttpRequest($request);
        $secondCopiedLocation = $response->getHeader('Location')[0];

        $request = $this->createHttpRequest(
            'POST',
            $newCopiedLocation,
            'SwapLocationInput+json',
            '',
            json_encode([
                'SwapLocationInput' => [
                    'destination' => str_replace(
                        '/api/ibexa/v2/content/locations',
                        '',
                        $secondCopiedLocation,
                    ),
                ],
            ], JSON_THROW_ON_ERROR),
        );

        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 204);
    }

    /**
     * @depends testMoveLocation
     */
    public function testCopy(string $locationHref): void
    {
        $request = $this->createHttpRequest(
            'POST',
            $locationHref,
            'CopyLocationInput+json',
            '',
            json_encode(['CopyLocationInput' => ['destination' => '/1/2']]) ?: '',
        );

        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');
    }
}
