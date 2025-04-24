<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Core\Repository\Values\Content\TrashItem;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\RestContent;
use Ibexa\Rest\Server\Values\RestTrashItem;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class RestTrashItemTest extends ValueObjectVisitorBaseTest
{
    public function testVisit(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $trashItem = new RestTrashItem(
            new TrashItem(
                [
                    'id' => 42,
                    'priority' => 0,
                    'hidden' => false,
                    'invisible' => true,
                    'remoteId' => 'remote-id',
                    'parentLocationId' => 21,
                    'pathString' => '/1/2/21/42/',
                    'depth' => 3,
                    'contentInfo' => new ContentInfo(
                        [
                            'id' => 84,
                             'contentTypeId' => 4,
                             'name' => 'A Node, long lost in the trash',
                        ]
                    ),
                    'sortField' => TrashItem::SORT_FIELD_NAME,
                    'sortOrder' => TrashItem::SORT_ORDER_DESC,
                ]
            ),
            // Dummy value for ChildCount
            0
        );

        $this->addRouteExpectation(
            'ibexa.rest.load_trash_item',
            ['trashItemId' => $trashItem->trashItem->id],
            "/content/trash/{$trashItem->trashItem->id}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_location',
            ['locationPath' => '1/2/21'],
            '/content/locations/1/2/21'
        );

        $this->addRouteExpectation(
            'ibexa.rest.load_content',
            ['contentId' => $trashItem->trashItem->contentInfo->id],
            "/content/objects/{$trashItem->trashItem->contentInfo->id}"
        );

        // Expected twice, second one here for ContentInfo
        $this->addRouteExpectation(
            'ibexa.rest.load_content',
            ['contentId' => $trashItem->trashItem->contentInfo->id],
            "/content/objects/{$trashItem->trashItem->contentInfo->id}"
        );

        $this->getVisitorMock()->expects(self::once())
            ->method('visitValueObject')
            ->with(self::isInstanceOf(RestContent::class));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $trashItem
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        return $result;
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsTrashItemElement($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'TrashItem',
                'children' => [
                    'count' => 12,
                ],
            ],
            $result,
            'Invalid <TrashItem> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsTrashItemAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'TrashItem',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.TrashItem+xml',
                    'href' => '/content/trash/42',
                ],
            ],
            $result,
            'Invalid <TrashItem> attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsContentInfoElement($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'ContentInfo',
            ],
            $result,
            'Invalid <ContentInfo> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsContentInfoAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'ContentInfo',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.ContentInfo+xml',
                    'href' => '/content/objects/84',
                ],
            ],
            $result,
            'Invalid <ContentInfo> attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsIdValueElement($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'id',
                'content' => '42',
            ],
            $result,
            'Invalid or non-existing <TrashItem> id value element.'
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsPriorityValueElement($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'priority',
                'content' => '0',
            ],
            $result,
            'Invalid or non-existing <TrashItem> priority value element.'
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsHiddenValueElement($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'hidden',
                'content' => 'false',
            ],
            $result,
            'Invalid or non-existing <TrashItem> hidden value element.',
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsInvisibleValueElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'invisible',
                'content' => 'true',
            ],
            $result,
            'Invalid or non-existing <TrashItem> invisible value element.'
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRemoteIdValueElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'remoteId',
                'content' => 'remote-id',
            ],
            $result,
            'Invalid or non-existing <TrashItem> remoteId value element.'
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsParentLocationElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'ParentLocation',
            ],
            $result,
            'Invalid <ParentLocation> element.'
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsParentLocationAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'ParentLocation',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.Location+xml',
                    'href' => '/content/locations/1/2/21',
                ],
            ],
            $result,
            'Invalid <ParentLocation> attributes.'
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsPathStringValueElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'pathString',
                'content' => '/1/2/21/42/',
            ],
            $result,
            'Invalid or non-existing <TrashItem> pathString value element.'
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsDepthValueElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'depth',
                'content' => '3',
            ],
            $result,
            'Invalid or non-existing <TrashItem> depth value element.'
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsChildCountValueElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'childCount',
                'content' => '0',
            ],
            $result,
            'Invalid or non-existing <TrashItem> childCount value element.',
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsContentElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Content',
            ],
            $result,
            'Invalid <Content> element.'
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsContentAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Content',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.Content+xml',
                    'href' => '/content/objects/84',
                ],
            ],
            $result,
            'Invalid <Content> attributes.'
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsSortFieldValueElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'sortField',
                'content' => 'NAME',
            ],
            $result,
            'Invalid or non-existing <TrashItem> sortField value element.'
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsSortOrderValueElement($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'sortOrder',
                'content' => 'DESC',
            ],
            $result,
            'Invalid or non-existing <TrashItem> sortOrder value element.'
        );
    }

    protected function internalGetVisitor(): ValueObjectVisitor\RestTrashItem
    {
        return new ValueObjectVisitor\RestTrashItem();
    }
}
