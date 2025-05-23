<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\URLWildcardList;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class URLWildcardListTest extends ValueObjectVisitorBaseTest
{
    public function testVisit(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $urlWildcardList = new URLWildcardList([]);

        $this->addRouteExpectation(
            'ibexa.rest.list_url_wildcards',
            [],
            '/content/urlwildcards'
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $urlWildcardList
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        return $result;
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsUrlWildcardListElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'UrlWildcardList',
            ],
            $result,
            'Invalid <UrlWildcardList> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsUrlWildcardListAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'UrlWildcardList',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.UrlWildcardList+xml',
                    'href' => '/content/urlwildcards',
                ],
            ],
            $result,
            'Invalid <UrlWildcardList> attributes.',
            false
        );
    }

    public function testURLWildcardListVisitsChildren(): void
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $urlWildcardList = new URLWildcardList(
            [
                new Content\URLWildcard(),
                new Content\URLWildcard(),
            ]
        );

        $this->getVisitorMock()->expects(self::exactly(2))
            ->method('visitValueObject')
            ->with(self::isInstanceOf(Content\URLWildcard::class));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $urlWildcardList
        );
    }

    protected function internalGetVisitor(): ValueObjectVisitor\URLWildcardList
    {
        return new ValueObjectVisitor\URLWildcardList();
    }
}
