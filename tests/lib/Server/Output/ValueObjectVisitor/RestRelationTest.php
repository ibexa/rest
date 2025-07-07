<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Core\Repository\Values\Content;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\RestRelation;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class RestRelationTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the RestRelation visitor.
     */
    public function testVisit(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $relation = new RestRelation(
            new Content\Relation(
                [
                    'id' => 42,
                    'sourceContentInfo' => new ContentInfo(
                        [
                            'id' => 1,
                        ]
                    ),
                    'destinationContentInfo' => new ContentInfo(
                        [
                            'id' => 2,
                        ]
                    ),
                    'type' => Content\Relation::FIELD,
                    'sourceFieldDefinitionIdentifier' => 'relation_field',
                ]
            ),
            1,
            1
        );

        $this->addRouteExpectation(
            'ibexa.rest.load_version_relation',
            [
                'contentId' => $relation->contentId,
                'versionNumber' => $relation->versionNo,
                'relationId' => $relation->relation->id,
            ],
            "/content/objects/{$relation->contentId}/versions/{$relation->versionNo}/relations/{$relation->relation->id}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_content',
            ['contentId' => $relation->contentId],
            "/content/objects/{$relation->contentId}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_content',
            ['contentId' => $relation->relation->getDestinationContentInfo()->id],
            "/content/objects/{$relation->relation->getDestinationContentInfo()->id}"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $relation
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        return $result;
    }

    /**
     * Test if result contains Relation element.
     *
     * @depends testVisit
     */
    public function testResultContainsRelationElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Relation',
                'children' => [
                    'less_than' => 5,
                    'greater_than' => 3,
                ],
            ],
            $result,
            'Invalid <Relation> element.',
            false
        );
    }

    /**
     * Test if result contains Relation element attributes.
     *
     * @depends testVisit
     */
    public function testResultContainsRelationAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Relation',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.Relation+xml',
                    'href' => '/content/objects/1/versions/1/relations/42',
                ],
            ],
            $result,
            'Invalid <Relation> attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsSourceContentElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'SourceContent',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.ContentInfo+xml',
                    'href' => '/content/objects/1',
                ],
            ],
            $result,
            'Invalid or non-existing <Relation> SourceContent element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsDestinationContentElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'DestinationContent',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.ContentInfo+xml',
                    'href' => '/content/objects/2',
                ],
            ],
            $result,
            'Invalid or non-existing <Relation> DestinationContent element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsSourceFieldDefinitionIdentifierElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'SourceFieldDefinitionIdentifier',
                'content' => 'relation_field',
            ],
            $result,
            'Invalid or non-existing <Relation> SourceFieldDefinitionIdentifier value element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRelationTypeElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'RelationType',
                'content' => 'ATTRIBUTE',
            ],
            $result,
            'Invalid or non-existing <Relation> RelationType value element.',
            false
        );
    }

    /**
     * Get the Relation visitor.
     */
    protected function internalGetVisitor(): ValueObjectVisitor\RestRelation
    {
        return new ValueObjectVisitor\RestRelation();
    }
}
