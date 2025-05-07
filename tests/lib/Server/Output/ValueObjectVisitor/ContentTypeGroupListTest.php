<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup;
use Ibexa\Core\Repository\Values\ContentType;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\ContentTypeGroupList;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class ContentTypeGroupListTest extends ValueObjectVisitorBaseTest
{
    public function testVisit(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $contentTypeGroupList = new ContentTypeGroupList([]);

        $this->addRouteExpectation('ibexa.rest.load_content_type_group_list', [], '/content/typegroups');

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $contentTypeGroupList
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        return $result;
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsContentTypeGroupListElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'ContentTypeGroupList',
            ],
            $result,
            'Invalid <ContentTypeGroupList> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsContentTypeGroupListAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'ContentTypeGroupList',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.ContentTypeGroupList+xml',
                    'href' => '/content/typegroups',
                ],
            ],
            $result,
            'Invalid <ContentTypeGroupList> attributes.',
            false
        );
    }

    public function testContentTypeGroupListVisitsChildren(): void
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $contentTypeGroupList = new ContentTypeGroupList(
            [
                new ContentType\ContentTypeGroup(),
                new ContentType\ContentTypeGroup(),
            ]
        );

        $this->getVisitorMock()->expects(self::exactly(2))
            ->method('visitValueObject')
            ->with(self::isInstanceOf(ContentTypeGroup::class));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $contentTypeGroupList
        );
    }

    protected function internalGetVisitor(): ValueObjectVisitor\ContentTypeGroupList
    {
        return new ValueObjectVisitor\ContentTypeGroupList();
    }
}
