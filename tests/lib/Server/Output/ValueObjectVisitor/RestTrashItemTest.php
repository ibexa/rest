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
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTestCase;
use PHPUnit\Framework\Attributes\Depends;

class RestTrashItemTest extends ValueObjectVisitorBaseTestCase
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

    #[Depends('testVisit')]
    public function testResultContainsTrashItemElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'TrashItem',
                'children' => [
                    'count' => 12,
                ],
            ],
            $result,
            'Invalid <TrashItem> element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsTrashItemAttributes(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'TrashItem',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.TrashItem+xml',
                    'href' => '/content/trash/42',
                ],
            ],
            $result,
            'Invalid <TrashItem> attributes.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsContentInfoElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'ContentInfo',
            ],
            $result,
            'Invalid <ContentInfo> element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsContentInfoAttributes(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'ContentInfo',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.ContentInfo+xml',
                    'href' => '/content/objects/84',
                ],
            ],
            $result,
            'Invalid <ContentInfo> attributes.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsIdValueElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'id',
                'content' => '42',
            ],
            $result,
            'Invalid or non-existing <TrashItem> id value element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsPriorityValueElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'priority',
                'content' => '0',
            ],
            $result,
            'Invalid or non-existing <TrashItem> priority value element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsHiddenValueElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'hidden',
                'content' => 'false',
            ],
            $result,
            'Invalid or non-existing <TrashItem> hidden value element.',
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsInvisibleValueElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'invisible',
                'content' => 'true',
            ],
            $result,
            'Invalid or non-existing <TrashItem> invisible value element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsRemoteIdValueElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'remoteId',
                'content' => 'remote-id',
            ],
            $result,
            'Invalid or non-existing <TrashItem> remoteId value element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsParentLocationElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'ParentLocation',
            ],
            $result,
            'Invalid <ParentLocation> element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsParentLocationAttributes(string $result): void
    {
        self::assertXMLTag(
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

    #[Depends('testVisit')]
    public function testResultContainsPathStringValueElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'pathString',
                'content' => '/1/2/21/42/',
            ],
            $result,
            'Invalid or non-existing <TrashItem> pathString value element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsDepthValueElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'depth',
                'content' => '3',
            ],
            $result,
            'Invalid or non-existing <TrashItem> depth value element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsChildCountValueElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'childCount',
                'content' => '0',
            ],
            $result,
            'Invalid or non-existing <TrashItem> childCount value element.',
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsContentElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'Content',
            ],
            $result,
            'Invalid <Content> element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsContentAttributes(string $result): void
    {
        self::assertXMLTag(
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

    #[Depends('testVisit')]
    public function testResultContainsSortFieldValueElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'sortField',
                'content' => 'NAME',
            ],
            $result,
            'Invalid or non-existing <TrashItem> sortField value element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsSortOrderValueElement(string $result): void
    {
        self::assertXMLTag(
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
