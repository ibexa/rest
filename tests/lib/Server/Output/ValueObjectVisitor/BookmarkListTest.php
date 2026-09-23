<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use DOMDocument;
use DOMNodeList;
use DOMXPath;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\BookmarkList;
use Ibexa\Rest\Server\Values\RestLocation;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTestCase;
use PHPUnit\Framework\Attributes\Depends;

class BookmarkListTest extends ValueObjectVisitorBaseTestCase
{
    private BookmarkList $data;

    protected function setUp(): void
    {
        $this->data = new BookmarkList(10, [
            new RestLocation(self::createStub(Location::class), 0),
            new RestLocation(self::createStub(Location::class), 0),
            new RestLocation(self::createStub(Location::class), 0),
        ]);
    }

    public function testVisit(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $this->data
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        return $result;
    }

    #[Depends('testVisit')]
    public function testResultContainsBookmarkListElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'BookmarkList',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.BookmarkList+xml',
                ],
            ],
            $result,
            'Invalid <BookmarkList> attributes.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsCountElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'count',
                'content' => $this->data->totalCount,
            ],
            $result
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsBookmarkElement(string $result): void
    {
        $query = "//BookmarkList/Bookmark[@media-type='application/vnd.ibexa.api.Bookmark+xml']";

        $document = new DOMDocument();
        $document->loadXML($result);
        $xpath = new DOMXPath($document);
        $queryResult = $xpath->query($query);

        self::assertInstanceOf(DOMNodeList::class, $queryResult);
        self::assertEquals(count($this->data->items), $queryResult->length);
    }

    /**
     * {@inheritdoc}
     */
    protected function internalGetVisitor(): ValueObjectVisitor\BookmarkList
    {
        return new ValueObjectVisitor\BookmarkList();
    }
}
