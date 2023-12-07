<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Field as ApiField;
use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Rest\Output\FieldTypeSerializer;
use Ibexa\Rest\Server\Output\ValueObjectVisitor\Field;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

/**
 * @covers \Ibexa\Rest\Server\Output\ValueObjectVisitor\Field
 */
final class FieldTest extends ValueObjectVisitorBaseTest
{
    /** @var \Ibexa\Rest\Output\FieldTypeSerializer&\PHPUnit\Framework\MockObject\MockObject */
    private FieldTypeSerializer $fieldTypeSerializer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fieldTypeSerializer = $this->createMock(FieldTypeSerializer::class);
    }

    public function testVisit(): void
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);
        $field = new ApiField(
            [
                'id' => 1,
                'fieldDefIdentifier' => 'foo',
                'value' => 'foo',
                'languageCode' => 'eng-GB',
                'fieldTypeIdentifier' => 'ezfoo',
            ]
        );

        $this->mockFieldTypeSerializerSerializeContentFieldValue(
            $generator,
            $field,
            '<value>foo</value>'
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $field
        );

        $result = $generator->endDocument(null);

        $this->assertContainsTag('field', $result);
        $this->assertContainsTag('id', $result);
        $this->assertContainsTag('fieldDefinitionIdentifier', $result);
        $this->assertContainsTag('value', $result);
        $this->assertContainsTag('languageCode', $result);
        $this->assertContainsTag('fieldTypeIdentifier', $result);
    }

    private function mockFieldTypeSerializerSerializeContentFieldValue(
        Generator $generator,
        ApiField $field,
        string $value
    ): void {
        $this->fieldTypeSerializer
            ->method('serializeContentFieldValue')
            ->with(
                $generator,
                $field
            )
            ->willReturn($value);
    }

    private function assertContainsTag(
        string $tag,
        string $result
    ): void {
        $this->assertXMLTag(
            [
                'tag' => $tag,
            ],
            $result,
            "Invalid <$tag> element.",
        );
    }

    protected function internalGetVisitor(): Field
    {
        return new Field($this->fieldTypeSerializer);
    }
}
