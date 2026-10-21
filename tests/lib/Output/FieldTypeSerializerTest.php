<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Output;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\FieldType as APIFieldType;
use Ibexa\Contracts\Core\Repository\FieldTypeService;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType as APIContentType;
use Ibexa\Contracts\Rest\FieldTypeProcessor;
use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Rest\FieldTypeProcessorRegistry;
use Ibexa\Rest\Output\FieldTypeSerializer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * FieldTypeSerializer test.
 */
class FieldTypeSerializerTest extends TestCase
{
    protected (MockObject & FieldTypeService) | null $fieldTypeServiceMock = null;

    protected (MockObject & FieldTypeProcessorRegistry) | null $fieldTypeProcessorRegistryMock = null;

    protected (MockObject & FieldTypeProcessor) | null $fieldTypeProcessorMock = null;

    protected (MockObject & APIContentType) | null $contentTypeMock = null;

    protected (MockObject & APIFieldType) | null $fieldTypeMock = null;

    protected (MockObject & Generator) | null $generatorMock = null;

    /**
     * @param array<int> $hashValue
     *
     * @throws NotFoundException
     */
    #[DataProvider('provideDataWithFieldValueToSerialize')]
    public function testSerializeContentFieldValue(
        array $hashValue
    ): void {
        // the mocked field type returns an empty identifier
        $this->expectNoFieldTypeProcessor('');
        $serializer = $this->getFieldTypeSerializer();
        $fieldType = $this->createFieldTypeMock('my-field-value', $hashValue);

        $this->mockFieldTypeServiceGetFieldType(
            'myFancyFieldType',
            $fieldType
        );

        $serializer->serializeContentFieldValue(
            $this->mockGeneratorGenerateFieldTypeHash(
                'fieldValue',
                $hashValue
            ),
            $this->createFieldMock('myFancyFieldType', 'my-field-value')
        );
    }

    /**
     * @return iterable<array{
     *     array<int>,
     * }>
     */
    public static function provideDataWithFieldValueToSerialize(): iterable
    {
        yield [[23, 42]];
    }

    public function testSerializeFieldDefaultValue(): void
    {
        // the mocked field type returns an empty identifier
        $this->expectNoFieldTypeProcessor('');
        $serializer = $this->getFieldTypeSerializer();

        $fieldTypeMock = $this->getFieldTypeMock();
        $this->mockFieldTypeServiceGetFieldType(
            'myFancyFieldType',
            $fieldTypeMock
        );

        $fieldTypeMock->expects($this->once())
            ->method('toHash')
            ->with(self::equalTo('my-field-value'))
            ->willReturn([23, 42]);

        $serializer->serializeFieldDefaultValue(
            $this->mockGeneratorGenerateFieldTypeHash(
                'defaultValue',
                [23, 42]
            ),
            'myFancyFieldType',
            'my-field-value'
        );
    }

    public function testSerializeFieldSettings(): void
    {
        $this->expectNoFieldTypeProcessor();
        $serializer = $this->getFieldTypeSerializer();

        $fieldTypeMock = $this->getFieldTypeMock();
        $this->mockFieldTypeServiceGetFieldType(
            'myFancyFieldType',
            $fieldTypeMock
        );

        $fieldTypeMock->expects($this->once())
            ->method('fieldSettingsToHash')
            ->with(self::equalTo('my-field-settings'))
            ->willReturn(['foo' => 'bar']);

        $serializer->serializeFieldSettings(
            $this->mockGeneratorGenerateFieldTypeHash(
                'fieldSettings',
                ['foo' => 'bar']
            ),
            'myFancyFieldType',
            'my-field-settings'
        );
    }

    public function testSerializeFieldSettingsWithPostProcessing(): void
    {
        $serializer = $this->getFieldTypeSerializer();
        $fieldTypeMock = $this->getFieldTypeMock();

        $processorMock = $this->getFieldTypeProcessorMock();
        $this->getFieldTypeProcessorRegistryMock()
            ->expects($this->once())
            ->method('hasProcessor')
            ->with('myFancyFieldType')
            ->willReturn(true);
        $this->getFieldTypeProcessorRegistryMock()
            ->expects($this->once())
            ->method('getProcessor')
            ->with('myFancyFieldType')
            ->willReturnCallback(
                static function () use ($processorMock): FieldTypeProcessor & MockObject {
                    return $processorMock;
                }
            );
        $processorMock->expects($this->once())
            ->method('postProcessFieldSettingsHash')
            ->with(self::equalTo(['foo' => 'bar']))
            ->willReturn(['post-processed']);

        $this->mockFieldTypeServiceGetFieldType(
            'myFancyFieldType',
            $fieldTypeMock
        );

        $fieldTypeMock->expects($this->once())
            ->method('fieldSettingsToHash')
            ->with(self::equalTo('my-field-settings'))
            ->willReturn(['foo' => 'bar']);

        $serializer->serializeFieldSettings(
            $this->mockGeneratorGenerateFieldTypeHash(
                'fieldSettings',
                ['post-processed']
            ),
            'myFancyFieldType',
            'my-field-settings'
        );
    }

    public function testSerializeValidatorConfiguration(): void
    {
        $this->expectNoFieldTypeProcessor();
        $serializer = $this->getFieldTypeSerializer();

        $fieldTypeMock = $this->getFieldTypeMock();
        $this->mockFieldTypeServiceGetFieldType(
            'myFancyFieldType',
            $fieldTypeMock
        );

        $fieldTypeMock->expects($this->once())
            ->method('validatorConfigurationToHash')
            ->with(self::equalTo('validator-config'))
            ->willReturn(['bar' => 'foo']);

        $serializer->serializeValidatorConfiguration(
            $this->mockGeneratorGenerateFieldTypeHash(
                'validatorConfiguration',
                ['bar' => 'foo']
            ),
            'myFancyFieldType',
            'validator-config'
        );
    }

    public function testSerializeValidatorConfigurationWithPostProcessing(): void
    {
        $serializer = $this->getFieldTypeSerializer();
        $fieldTypeMock = $this->getFieldTypeMock();

        $processorMock = $this->getFieldTypeProcessorMock();
        $this->getFieldTypeProcessorRegistryMock()
            ->expects($this->once())
            ->method('hasProcessor')
            ->with('myFancyFieldType')
            ->willReturn(true);
        $this->getFieldTypeProcessorRegistryMock()
            ->expects($this->once())
            ->method('getProcessor')
            ->with('myFancyFieldType')
            ->willReturnCallback(
                static function () use ($processorMock): FieldTypeProcessor & MockObject {
                    return $processorMock;
                }
            );
        $processorMock->expects($this->once())
            ->method('postProcessValidatorConfigurationHash')
            ->with(self::equalTo(['bar' => 'foo']))
            ->willReturn(['post-processed']);

        $fieldTypeMock = $this->getFieldTypeMock();
        $this->mockFieldTypeServiceGetFieldType(
            'myFancyFieldType',
            $fieldTypeMock
        );

        $fieldTypeMock->expects($this->once())
            ->method('validatorConfigurationToHash')
            ->with(self::equalTo('validator-config'))
            ->willReturn(['bar' => 'foo']);

        $serializer->serializeValidatorConfiguration(
            $this->mockGeneratorGenerateFieldTypeHash(
                'validatorConfiguration',
                ['post-processed']
            ),
            'myFancyFieldType',
            'validator-config'
        );
    }

    protected function getFieldTypeSerializer(): FieldTypeSerializer
    {
        return new FieldTypeSerializer(
            $this->getFieldTypeServiceMock(),
            $this->getFieldTypeProcessorRegistryMock()
        );
    }

    protected function getFieldTypeServiceMock(): FieldTypeService & MockObject
    {
        if (!isset($this->fieldTypeServiceMock)) {
            $this->fieldTypeServiceMock = $this->createMock(FieldTypeService::class);
        }

        return $this->fieldTypeServiceMock;
    }

    protected function getFieldTypeProcessorRegistryMock(): FieldTypeProcessorRegistry & MockObject
    {
        if (!isset($this->fieldTypeProcessorRegistryMock)) {
            $this->fieldTypeProcessorRegistryMock = $this->createMock(FieldTypeProcessorRegistry::class);
        }

        return $this->fieldTypeProcessorRegistryMock;
    }

    protected function getFieldTypeProcessorMock(): FieldTypeProcessor & MockObject
    {
        if (!isset($this->fieldTypeProcessorMock)) {
            $this->fieldTypeProcessorMock = $this->createMock(FieldTypeProcessor::class);
        }

        return $this->fieldTypeProcessorMock;
    }

    protected function getContentTypeMock(): APIContentType & MockObject
    {
        if (!isset($this->contentTypeMock)) {
            $this->contentTypeMock = $this->createMock(APIContentType::class);
        }

        return $this->contentTypeMock;
    }

    protected function getFieldTypeMock(): APIFieldType & MockObject
    {
        if (!isset($this->fieldTypeMock)) {
            $this->fieldTypeMock = $this->createMock(APIFieldType::class);
        }

        return $this->fieldTypeMock;
    }

    private function createFieldMock(
        string $fieldTypeIdentifier,
        string $value
    ): Field {
        $fieldMock = self::createStub(Field::class);
        $fieldMock
            ->method('getFieldTypeIdentifier')
            ->willReturn($fieldTypeIdentifier);

        $fieldMock
            ->method('getValue')
            ->willReturn($value);

        return $fieldMock;
    }

    /**
     * @param array<int|string, int|string> $hashElementValue
     */
    private function mockGeneratorGenerateFieldTypeHash(
        string $hashElementName,
        array $hashElementValue
    ): Generator {
        $generator = $this->createMock(Generator::class);
        $generator
            ->expects($this->once())
            ->method('generateFieldTypeHash')
            ->with(
                $hashElementName,
                $hashElementValue
            );

        return $generator;
    }

    /**
     * @param int[] $hashValue
     */
    private function createFieldTypeMock(
        string $value,
        array $hashValue
    ): APIFieldType {
        $fieldTypeMock = $this->createMock(APIFieldType::class);
        $fieldTypeMock
            ->expects($this->once())
            ->method('toHash')
            ->with($value)
            ->willReturn($hashValue);

        return $fieldTypeMock;
    }

    private function expectNoFieldTypeProcessor(string $fieldTypeIdentifier = 'myFancyFieldType'): void
    {
        $this->getFieldTypeProcessorRegistryMock()
            ->expects($this->once())
            ->method('hasProcessor')
            ->with($fieldTypeIdentifier)
            ->willReturn(false);
    }

    private function mockFieldTypeServiceGetFieldType(
        string $identifier,
        APIFieldType $fieldType,
    ): void {
        $this->getFieldTypeServiceMock()
            ->expects($this->once())
            ->method('getFieldType')
            ->with($identifier)
            ->willReturn($fieldType);
    }
}
