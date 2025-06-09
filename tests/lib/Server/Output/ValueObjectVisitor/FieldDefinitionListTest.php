<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Core\Repository\Values;
use Ibexa\Rest\Server;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\FieldDefinitionList;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

/**
 * @todo coverage add unit test for a content type draft
 */
class FieldDefinitionListTest extends ValueObjectVisitorBaseTest
{
    public function testVisitFieldDefinitionList(): \DOMDocument
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $fieldDefinitionList = $this->getBasicFieldDefinitionList();

        $this->getVisitorMock()->expects(self::exactly(2))
            ->method('visitValueObject')
            ->with(self::isInstanceOf(Server\Values\RestFieldDefinition::class));

        $this->addRouteExpectation(
            'ibexa.rest.load_content_type_field_definition_list',
            ['contentTypeId' => $fieldDefinitionList->contentType->id],
            "/content/types/{$fieldDefinitionList->contentType->id}/fieldDefinitions"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $fieldDefinitionList
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        $dom = new \DOMDocument();
        $dom->loadXml($result);

        return $dom;
    }

    protected function getBasicFieldDefinitionList(): FieldDefinitionList
    {
        return new FieldDefinitionList(
            new Values\ContentType\ContentType(
                [
                    'id' => 12,
                    'status' => Values\ContentType\ContentType::STATUS_DEFINED,
                    'fieldDefinitions' => new Values\ContentType\FieldDefinitionCollection(),
                ]
            ),
            [
                new Values\ContentType\FieldDefinition(
                    ['id' => 1]
                ),
                new Values\ContentType\FieldDefinition(
                    ['id' => 2]
                ),
            ]
        );
    }

    /**
     * @return array<int, array<string>>
     */
    public function provideXpathAssertions(): array
    {
        return [
            [
                '/FieldDefinitions[@href="/content/types/contentTypeId/fieldDefinitions"]',
            ],
            [
                '/FieldDefinitions[@media-type="application/vnd.ibexa.api.FieldDefinitionList+xml"]',
            ],
        ];
    }

    /**
     * @depends testVisitFieldDefinitionList
     *
     * @dataProvider provideXpathAssertions
     */
    public function testGeneratedXml(string $xpath, \DOMDocument $dom): void
    {
        $this->assertXPath($dom, $xpath);
    }

    protected function internalGetVisitor(): ValueObjectVisitor\FieldDefinitionList
    {
        return new ValueObjectVisitor\FieldDefinitionList();
    }
}
