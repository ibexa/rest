<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Core\Repository\Values\Content;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\RestTrashItem;
use Ibexa\Rest\Server\Values\Trash;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class TrashTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the Trash visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $trash = new Trash([], '/content/trash');

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $trash
        );

        $result = $generator->endDocument(null);

        self::assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains Trash element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsTrashElement($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Trash',
            ],
            $result,
            'Invalid <Trash> element.',
            false
        );
    }

    /**
     * Test if result contains Trash element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsTrashAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Trash',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.Trash+xml',
                    'href' => '/content/trash',
                ],
            ],
            $result,
            'Invalid <Trash> attributes.',
            false
        );
    }

    /**
     * Test if Trash visitor visits the children.
     */
    public function testTrashVisitsChildren(): void
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $trashList = new Trash(
            [
                new RestTrashItem(
                    new Content\TrashItem(),
                    // Dummy value for ChildCount
                    0
                ),
                new RestTrashItem(
                    new Content\TrashItem(),
                    // Dummy value for ChildCount
                    0
                ),
            ],
            '/content/trash'
        );

        $this->getVisitorMock()->expects(self::exactly(2))
            ->method('visitValueObject')
            ->with(self::isInstanceOf(RestTrashItem::class));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $trashList
        );
    }

    /**
     * Get the Trash visitor.
     *
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\Trash
     */
    protected function internalGetVisitor(): ValueObjectVisitor\Trash
    {
        return new ValueObjectVisitor\Trash();
    }
}
