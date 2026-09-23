<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\ContentList;
use Ibexa\Rest\Server\Values\RestContent;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTestCase;
use PHPUnit\Framework\Attributes\Depends;

class ContentListTest extends ValueObjectVisitorBaseTestCase
{
    public function testVisit(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $contentList = new ContentList([], 0);

        $this->addRouteExpectation(
            'ibexa.rest.redirect_content',
            [],
            '/content/objects'
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $contentList
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        return $result;
    }

    #[Depends('testVisit')]
    public function testResultContainsContentListElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'ContentList',
            ],
            $result,
            'Invalid <ContentList> element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsContentListAttributes(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'ContentList',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.ContentList+xml',
                    'href' => '/content/objects',
                ],
            ],
            $result,
            'Invalid <ContentList> attributes.'
        );
    }

    public function testContentListVisitsChildren(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $contentList = new ContentList(
            [
                new RestContent(new ContentInfo()),
                new RestContent(new ContentInfo()),
            ],
            2
        );

        $this->getVisitorMock()->expects(self::exactly(2))
            ->method('visitValueObject')
            ->with(self::isInstanceOf(RestContent::class));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $contentList
        );

        return $generator->endDocument(null);
    }

    #[Depends('testContentListVisitsChildren')]
    public function testResultContainsTotalCountAttributes(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'ContentList',
                'attributes' => [
                    'totalCount' => 2,
                ],
            ],
            $result,
            'Invalid <ContentList> totalCount attribute.'
        );
    }

    /**
     * Get the ContentList visitor.
     */
    protected function internalGetVisitor(): ValueObjectVisitor\ContentList
    {
        return new ValueObjectVisitor\ContentList();
    }
}
