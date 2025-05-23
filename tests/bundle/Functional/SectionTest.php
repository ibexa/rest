<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Bundle\Rest\Functional;

use Ibexa\Tests\Bundle\Rest\Functional\TestCase as RESTFunctionalTestCase;

class SectionTest extends RESTFunctionalTestCase
{
    /**
     * Covers GET /content/sections.
     */
    public function testListSections(): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ibexa/v2/content/sections')
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers POST /content/sections.
     *
     * @return string The created section href
     */
    public function testCreateSection()
    {
        $xml = <<< XML
<SectionInput>
  <identifier>testCreateSection</identifier>
  <name>testCreateSection</name>
</SectionInput>
XML;
        $request = $this->createHttpRequest(
            'POST',
            '/api/ibexa/v2/content/sections',
            'SectionInput+xml',
            'Section+json',
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
     * @param $sectionHref
     *
     * @depends testCreateSection
     * Covers PATCH /content/sections/{sectionId}
     */
    public function testUpdateSection(string $sectionHref): void
    {
        $xml = <<< XML
<SectionInput>
  <identifier>testUpdateSection</identifier>
  <name>testUpdateSection</name>
</SectionInput>
XML;
        $request = $this->createHttpRequest(
            'PATCH',
            $sectionHref,
            'SectionInput+xml',
            'Section+json',
            $xml
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers GET /content/sections/{sectionId}.
     *
     * @depends testCreateSection
     */
    public function testLoadSection(string $sectionHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $sectionHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateSection
     * Covers GET /content/sections?identifier={sectionIdentifier}
     */
    public function testLoadSectionByIdentifier($sectionHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ibexa/v2/content/sections?identifier=testUpdateSection')
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateSection
     * Covers DELETE /content/sections/{sectionId}
     */
    public function testDeleteSection(string $sectionHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $sectionHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }
}
