<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\FieldType;
use Ibexa\Contracts\Core\Repository\FieldTypeService;
use Ibexa\Contracts\Core\Repository\Values\Content\Field as ApiField;
use Ibexa\Rest\FieldTypeProcessorRegistry;
use Ibexa\Rest\Output\FieldTypeSerializer;
use Ibexa\Rest\Server\Output\ValueObjectVisitor\Field;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

/**
 * @covers \Ibexa\Rest\Server\Output\ValueObjectVisitor\Field
 */
final class FieldTest extends ValueObjectVisitorBaseTest
{
    /** @var \Ibexa\Contracts\Core\Repository\FieldTypeService&\PHPUnit\Framework\MockObject\MockObject */
    private FieldTypeService $fieldTypeService;

    /** @var \Ibexa\Rest\FieldTypeProcessorRegistry&\PHPUnit\Framework\MockObject\MockObject */
    private FieldTypeProcessorRegistry $fieldTypeProcessorRegistry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fieldTypeService = $this->createMock(FieldTypeService::class);
        $this->fieldTypeProcessorRegistry = $this->createMock(FieldTypeProcessorRegistry::class);
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
                'fieldTypeIdentifier' => 'foo_field_type',
            ]
        );

        $this->mockSerializingFieldValue($field);

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

    private function mockSerializingFieldValue(ApiField $field): void
    {
        $fieldTypeMock = $this->createMock(FieldType::class);
        $fieldTypeIdentifier = $field->getFieldTypeIdentifier();

        $fieldTypeMock->method('getFieldTypeIdentifier')->willReturn($fieldTypeIdentifier);
        $fieldTypeMock->method('toHash')->with('foo')->willReturn(['value' => 'foo']);

        $this->fieldTypeProcessorRegistry->method('hasProcessor')->with($fieldTypeIdentifier)->willReturn(false);

        $this->fieldTypeService->method('getFieldType')->with($fieldTypeIdentifier)->willReturn($fieldTypeMock);
    }

    private function assertContainsTag(
        string $tag,
        string $result
    ): void {
        self::assertXMLTag(
            [
                'tag' => $tag,
            ],
            $result,
            "Invalid <$tag> element.",
        );
    }

    protected function internalGetVisitor(): Field
    {
        return new Field(
            new FieldTypeSerializer(
                $this->fieldTypeService,
                $this->fieldTypeProcessorRegistry
            )
        );
    }
}
