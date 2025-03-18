<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Bundle\Rest\Functional;

use Ibexa\Tests\Bundle\Rest\Functional\TestCase as RESTFunctionalTestCase;

class UrlWildcardTest extends RESTFunctionalTestCase
{
    /**
     * Covers GET /content/urlwildcards.
     */
    public function testListURLWildcards(): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ibexa/v2/content/urlwildcards')
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @returns string The created URL wildcard href
     * Covers POST /content/urlwildcards
     */
    public function testCreateUrlWildcard()
    {
        $text = $this->addTestSuffix(__FUNCTION__);
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<UrlWildcardCreate>
  <sourceUrl>/{$text}/*</sourceUrl>
  <destinationUrl>/destination/url/{1}</destinationUrl>
  <forward>true</forward>
</UrlWildcardCreate>
XML;

        $request = $this->createHttpRequest(
            'POST',
            '/api/ibexa/v2/content/urlwildcards',
            'UrlWildcardCreate+xml',
            'UrlWildcard+json',
            $xml
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        $href = $response->getHeader('Location')[0];
        $this->addCreatedElement($href);

        return $href;
    }

    /**
     * @param $urlWildcardHref Covers GET /content/urlwildcards/{urlWildcardId}
     *
     * @depends testCreateUrlWildcard
     */
    public function testLoadUrlWildcard(string $urlWildcardHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $urlWildcardHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @param $urlWildcardHref
     *
     * @depends testCreateUrlWildcard
     */
    public function testDeleteURLWildcard(string $urlWildcardHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $urlWildcardHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }
}
