<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\URLAliasList;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class URLAliasListTest extends ValueObjectVisitorBaseTest
{
    public function testVisit(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $urlAliasList = new URLAliasList([], '/content/urlaliases');

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $urlAliasList
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        return $result;
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsUrlAliasListElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'UrlAliasList',
            ],
            $result,
            'Invalid <UrlAliasList> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsUrlAliasListAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'UrlAliasList',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.UrlAliasList+xml',
                    'href' => '/content/urlaliases',
                ],
            ],
            $result,
            'Invalid <UrlAliasList> attributes.',
            false
        );
    }

    public function testURLAliasListVisitsChildren(): void
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $urlAliasList = new URLAliasList(
            [
                new Content\URLAlias(),
                new Content\URLAlias(),
            ],
            '/content/urlaliases'
        );

        $this->getVisitorMock()->expects(self::exactly(2))
            ->method('visitValueObject')
            ->with(self::isInstanceOf(Content\URLAlias::class));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $urlAliasList
        );
    }

    protected function internalGetVisitor(): ValueObjectVisitor\URLAliasList
    {
        return new ValueObjectVisitor\URLAliasList();
    }
}
