<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Output;

use Ibexa\Contracts\Core\Repository\FieldType as APIFieldType;
use Ibexa\Contracts\Core\Repository\FieldTypeService;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType as APIContentType;
use Ibexa\Contracts\Rest\FieldTypeProcessor;
use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Rest\FieldTypeProcessorRegistry;
use Ibexa\Rest\Output\FieldTypeSerializer;
use PHPUnit\Framework\TestCase;

/**
 * FieldTypeSerializer test.
 */
class FieldTypeSerializerTest extends TestCase
{
    protected $fieldTypeServiceMock;

    protected $fieldTypeProcessorRegistryMock;

    protected $fieldTypeProcessorMock;

    protected $contentTypeMock;

    protected $fieldTypeMock;

    protected $generatorMock;

    /**
     * @dataProvider provideDataWithFieldValueToSerialize
     *
     * @param mixed $hashValue
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function testSerializeContentFieldValue(
        APIFieldType $fieldType,
        $hashValue
    ): void {
        $serializer = $this->getFieldTypeSerializer();

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
     *     APIFieldType,
     *     array<int>,
     * }>
     */
    public function provideDataWithFieldValueToSerialize(): iterable
    {
        $hash = [23, 42];
        yield [
            $this->createFieldTypeMock(
                'my-field-value',
                $hash
            ),
            $hash,
        ];
    }

    public function testSerializeFieldDefaultValue(): void
    {
        $serializer = $this->getFieldTypeSerializer();

        $fieldTypeMock = $this->getFieldTypeMock();
        $this->mockFieldTypeServiceGetFieldType(
            'myFancyFieldType',
            $fieldTypeMock
        );

        $fieldTypeMock->expects(self::once())
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
        $serializer = $this->getFieldTypeSerializer();

        $fieldTypeMock = $this->getFieldTypeMock();
        $this->mockFieldTypeServiceGetFieldType(
            'myFancyFieldType',
            $fieldTypeMock
        );

        $fieldTypeMock->expects(self::once())
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
            ->expects(self::once())
            ->method('hasProcessor')
            ->with('myFancyFieldType')
            ->willReturn(true);
        $this->getFieldTypeProcessorRegistryMock()
            ->expects(self::once())
            ->method('getProcessor')
            ->with('myFancyFieldType')
            ->willReturnCallback(
                static function () use ($processorMock) {
                    return $processorMock;
                }
            );
        $processorMock->expects(self::once())
            ->method('postProcessFieldSettingsHash')
            ->with(self::equalTo(['foo' => 'bar']))
            ->willReturn(['post-processed']);

        $this->mockFieldTypeServiceGetFieldType(
            'myFancyFieldType',
            $fieldTypeMock
        );

        $fieldTypeMock->expects(self::once())
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
        $serializer = $this->getFieldTypeSerializer();

        $fieldTypeMock = $this->getFieldTypeMock();
        $this->mockFieldTypeServiceGetFieldType(
            'myFancyFieldType',
            $fieldTypeMock
        );

        $fieldTypeMock->expects(self::once())
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
            ->expects(self::once())
            ->method('hasProcessor')
            ->with('myFancyFieldType')
            ->willReturn(true);
        $this->getFieldTypeProcessorRegistryMock()
            ->expects(self::once())
            ->method('getProcessor')
            ->with('myFancyFieldType')
            ->willReturnCallback(
                static function () use ($processorMock) {
                    return $processorMock;
                }
            );
        $processorMock->expects(self::once())
            ->method('postProcessValidatorConfigurationHash')
            ->with(self::equalTo(['bar' => 'foo']))
            ->willReturn(['post-processed']);

        $fieldTypeMock = $this->getFieldTypeMock();
        $this->mockFieldTypeServiceGetFieldType(
            'myFancyFieldType',
            $fieldTypeMock
        );

        $fieldTypeMock->expects(self::once())
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

    protected function getFieldTypeServiceMock()
    {
        if (!isset($this->fieldTypeServiceMock)) {
            $this->fieldTypeServiceMock = $this->createMock(FieldTypeService::class);
        }

        return $this->fieldTypeServiceMock;
    }

    protected function getFieldTypeProcessorRegistryMock()
    {
        if (!isset($this->fieldTypeProcessorRegistryMock)) {
            $this->fieldTypeProcessorRegistryMock = $this->createMock(FieldTypeProcessorRegistry::class);
        }

        return $this->fieldTypeProcessorRegistryMock;
    }

    protected function getFieldTypeProcessorMock()
    {
        if (!isset($this->fieldTypeProcessorMock)) {
            $this->fieldTypeProcessorMock = $this->createMock(FieldTypeProcessor::class);
        }

        return $this->fieldTypeProcessorMock;
    }

    protected function getContentTypeMock()
    {
        if (!isset($this->contentTypeMock)) {
            $this->contentTypeMock = $this->createMock(APIContentType::class);
        }

        return $this->contentTypeMock;
    }

    protected function getFieldTypeMock()
    {
        if (!isset($this->fieldTypeMock)) {
            $this->fieldTypeMock = $this->createMock(APIFieldType::class);
        }

        return $this->fieldTypeMock;
    }

    /**
     * @param mixed $value
     */
    private function createFieldMock(
        string $fieldTypeIdentifier,
        $value
    ): Field {
        $fieldMock = $this->createMock(Field::class);
        $fieldMock
            ->method('getFieldTypeIdentifier')
            ->willReturn($fieldTypeIdentifier);

        $fieldMock
            ->method('getValue')
            ->willReturn($value);

        return $fieldMock;
    }

    /**
     * @param mixed $hashElementName
     * @param mixed $hashElementValue
     */
    private function mockGeneratorGenerateFieldTypeHash(
        $hashElementName,
        $hashElementValue
    ): Generator {
        $generator = $this->createMock(Generator::class);
        $generator
            ->method('generateFieldTypeHash')
            ->with(
                $hashElementName,
                $hashElementValue
            );

        return $generator;
    }

    /**
     * @param mixed $value
     * @param mixed $hashValue
     */
    private function createFieldTypeMock(
        $value,
        $hashValue
    ): APIFieldType {
        $fieldTypeMock = $this->createMock(APIFieldType::class);
        $fieldTypeMock
            ->method('toHash')
            ->with($value)
            ->willReturn($hashValue);

        return $fieldTypeMock;
    }

    private function mockFieldTypeServiceGetFieldType(
        string $identifier,
        APIFieldType $fieldType
    ): void {
        $this->getFieldTypeServiceMock()
            ->method('getFieldType')
            ->with($identifier)
            ->willReturn($fieldType);
    }
}
