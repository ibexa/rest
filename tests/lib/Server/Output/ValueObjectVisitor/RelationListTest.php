<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Core\Repository\Values\Content;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\RelationList;
use Ibexa\Rest\Server\Values\RestRelation;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class RelationListTest extends ValueObjectVisitorBaseTest
{
    public function testVisit(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $relationList = new RelationList([], 42, 21);

        $this->addRouteExpectation(
            'ibexa.rest.load_version_relations',
            [
                'contentId' => $relationList->contentId,
                'versionNumber' => $relationList->versionNo,
            ],
            "/content/objects/{$relationList->contentId}/versions/{$relationList->versionNo}/relations"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $relationList
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        return $result;
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRelationsElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Relations',
            ],
            $result,
            'Invalid <Relations> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRelationsAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Relations',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.RelationList+xml',
                    'href' => '/content/objects/42/versions/21/relations',
                ],
            ],
            $result,
            'Invalid <Relations> attributes.',
            false
        );
    }

    public function testRelationListVisitsChildren(): void
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $relationList = new RelationList(
            [
                new Content\Relation(),
                new Content\Relation(),
            ],
            23,
            1
        );

        $this->getVisitorMock()->expects(self::exactly(2))
            ->method('visitValueObject')
            ->with(self::isInstanceOf(RestRelation::class));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $relationList
        );
    }

    protected function internalGetVisitor(): ValueObjectVisitor\RelationList
    {
        return new ValueObjectVisitor\RelationList();
    }
}
