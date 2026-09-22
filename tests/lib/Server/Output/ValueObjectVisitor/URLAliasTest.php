<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content;
use Ibexa\Rest\Server\Output\ValueObjectVisitor\URLAlias;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTestCase;
use PHPUnit\Framework\Attributes\Depends;

class URLAliasTest extends ValueObjectVisitorBaseTestCase
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

    #[Depends('testVisit')]
    public function testResultContainsUrlAliasElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'UrlAlias',
                'children' => [
                    'less_than' => 8,
                    'greater_than' => 6,
                ],
            ],
            $result,
            'Invalid <UrlAlias> element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsUrlAliasAttributes(string $result): void
    {
        self::assertXMLTag(
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
            'Invalid <UrlAlias> attributes.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsUrlValueElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'resource',
                'content' => '/destination/url',
            ],
            $result,
            'Invalid or non-existing <UrlAlias> url value element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsPathValueElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'path',
                'content' => '/some/path',
            ],
            $result,
            'Invalid or non-existing <UrlAlias> path value element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsLanguageCodesValueElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'languageCodes',
                'content' => 'eng-GB,eng-US',
            ],
            $result,
            'Invalid or non-existing <UrlAlias> languageCodes value element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsAlwaysAvailableValueElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'alwaysAvailable',
                'content' => 'true',
            ],
            $result,
            'Invalid or non-existing <UrlAlias> alwaysAvailable value element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsIsHistoryValueElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'isHistory',
                'content' => 'true',
            ],
            $result,
            'Invalid or non-existing <UrlAlias> isHistory value element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsForwardValueElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'forward',
                'content' => 'false',
            ],
            $result,
            'Invalid or non-existing <UrlAlias> forward value element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsCustomValueElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'custom',
                'content' => 'false',
            ],
            $result,
            'Invalid or non-existing <UrlAlias> custom value element.'
        );
    }

    protected function internalGetVisitor(): URLAlias
    {
        return new URLAlias();
    }
}
