<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content;
use Ibexa\Rest\Server\Output\ValueObjectVisitor\URLAlias;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class URLAliasTest extends ValueObjectVisitorBaseTest
{
    public function testVisit(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $urlAlias = new Content\URLAlias(
            [
                'id' => 'some-id',
                'type' => 1,
                'destination' => '/destination/url',
                'path' => '/some/path',
                'languageCodes' => ['eng-GB', 'eng-US'],
                'alwaysAvailable' => true,
                'isHistory' => true,
                'isCustom' => false,
                'forward' => false,
            ]
        );

        $this->addRouteExpectation(
            'ibexa.rest.load_url_alias',
            ['urlAliasId' => $urlAlias->id],
            "/content/urlaliases/{$urlAlias->id}"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $urlAlias
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        return $result;
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsUrlAliasElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'UrlAlias',
                'children' => [
                    'less_than' => 8,
                    'greater_than' => 6,
                ],
            ],
            $result,
            'Invalid <UrlAlias> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsUrlAliasAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'UrlAlias',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.UrlAlias+xml',
                    'href' => '/content/urlaliases/some-id',
                    'id' => 'some-id',
                    'type' => 'RESOURCE',
                ],
            ],
            $result,
            'Invalid <UrlAlias> attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsUrlValueElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'resource',
                'content' => '/destination/url',
            ],
            $result,
            'Invalid or non-existing <UrlAlias> url value element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsPathValueElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'path',
                'content' => '/some/path',
            ],
            $result,
            'Invalid or non-existing <UrlAlias> path value element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsLanguageCodesValueElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'languageCodes',
                'content' => 'eng-GB,eng-US',
            ],
            $result,
            'Invalid or non-existing <UrlAlias> languageCodes value element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsAlwaysAvailableValueElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'alwaysAvailable',
                'content' => 'true',
            ],
            $result,
            'Invalid or non-existing <UrlAlias> alwaysAvailable value element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsIsHistoryValueElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'isHistory',
                'content' => 'true',
            ],
            $result,
            'Invalid or non-existing <UrlAlias> isHistory value element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsForwardValueElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'forward',
                'content' => 'false',
            ],
            $result,
            'Invalid or non-existing <UrlAlias> forward value element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsCustomValueElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'custom',
                'content' => 'false',
            ],
            $result,
            'Invalid or non-existing <UrlAlias> custom value element.',
            false
        );
    }

    protected function internalGetVisitor(): URLAlias
    {
        return new URLAlias();
    }
}
