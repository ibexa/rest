<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Input;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\FieldType;
use Ibexa\Contracts\Core\Repository\FieldTypeService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Rest\FieldTypeProcessor;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Rest\FieldTypeProcessorRegistry;
use Ibexa\Rest\Input\FieldTypeParser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * FieldTypeParser test class.
 */
class FieldTypeParserTest extends TestCase
{
    protected MockObject $contentServiceMock;

    protected MockObject $contentTypeServiceMock;

    protected MockObject $fieldTypeServiceMock;

    protected MockObject $contentTypeMock;

    protected MockObject $fieldTypeMock;

    protected MockObject $fieldTypeProcessorRegistryMock;

    protected MockObject $fieldTypeProcessorMock;

    public function setUp(): void
    {
        $this->contentServiceMock = $this->createMock(ContentService::class);
        $this->contentTypeServiceMock = $this->createMock(ContentTypeService::class);
        $this->fieldTypeServiceMock = $this->createMock(FieldTypeService::class);
        $this->contentTypeMock = $this->createMock(ContentType::class);
        $this->fieldTypeMock = $this->createMock(FieldType::class);
        $this->fieldTypeProcessorRegistryMock = $this->createMock(FieldTypeProcessorRegistry::class);
        $this->fieldTypeProcessorMock = $this->createMock(FieldTypeProcessor::class);
    }

    public function testParseFieldValue(): void
    {
        $fieldTypeParser = $this->getFieldTypeParser();

        $this->contentServiceMock->expects(self::once())
            ->method('loadContentInfo')
            ->with('23')
            ->willReturn(
                new ContentInfo(['contentTypeId' => '42'])
            );

        $contentTypeMock = $this->contentTypeMock;
        $this->contentTypeServiceMock->expects(self::once())
            ->method('loadContentType')
            ->with('42')
            ->willReturnCallback(
                // Avoid PHPUnit cloning
                static function () use ($contentTypeMock) {
                    return $contentTypeMock;
                }
            );

        $contentTypeMock->expects(self::once())
            ->method('getFieldDefinition')
            ->with(self::equalTo('my-field-definition'))
            ->willReturn(
                new FieldDefinition(
                    [
                        'fieldTypeIdentifier' => 'some-fancy-field-type',
                    ]
                )
            );

        $this->fieldTypeProcessorRegistryMock->expects(self::once())
            ->method('hasProcessor')
            ->with(self::equalTo('some-fancy-field-type'))
            ->willReturn(false);

        $fieldTypeMock = $this->fieldTypeMock;
        $this->fieldTypeServiceMock->expects(self::once())
            ->method('getFieldType')
            ->with(self::equalTo('some-fancy-field-type'))
            ->willReturnCallback(
                // Avoid PHPUnit cloning
                static function () use ($fieldTypeMock) {
                    return $fieldTypeMock;
                }
            );

        $fieldTypeMock->expects(self::once())
            ->method('fromHash')
            ->with(self::equalTo([1, 2, 3]))
            ->willReturn(['foo', 'bar']);

        self::assertEquals(
            ['foo', 'bar'],
            $fieldTypeParser->parseFieldValue(
                '23',
                'my-field-definition',
                [1, 2, 3]
            )
        );
    }

    public function testParseValue(): void
    {
        $fieldTypeParser = $this->getFieldTypeParser();

        $this->fieldTypeProcessorRegistryMock->expects(self::once())
            ->method('hasProcessor')
            ->with(self::equalTo('some-fancy-field-type'))
            ->willReturn(false);

        $fieldTypeMock = $this->fieldTypeMock;
        $this->fieldTypeServiceMock->expects(self::once())
            ->method('getFieldType')
            ->with(self::equalTo('some-fancy-field-type'))
            ->willReturnCallback(
                // Avoid PHPUnit cloning
                static function () use ($fieldTypeMock) {
                    return $fieldTypeMock;
                }
            );

        $fieldTypeMock->expects(self::once())
            ->method('fromHash')
            ->with(self::equalTo([1, 2, 3]))
            ->willReturn(['foo', 'bar']);

        self::assertEquals(
            ['foo', 'bar'],
            $fieldTypeParser->parseValue(
                'some-fancy-field-type',
                [1, 2, 3]
            )
        );
    }

    public function testParseValueWithPreProcessing(): void
    {
        $fieldTypeParser = $this->getFieldTypeParser();

        $this->fieldTypeProcessorRegistryMock->expects(self::once())
            ->method('hasProcessor')
            ->with(self::equalTo('some-fancy-field-type'))
            ->willReturn(true);

        $processor = $this->fieldTypeProcessorMock;
        $this->fieldTypeProcessorRegistryMock->expects(self::once())
            ->method('getProcessor')
            ->with(self::equalTo('some-fancy-field-type'))
            ->willReturnCallback(
                static function () use ($processor) {
                    return $processor;
                }
            );

        $processor->expects(self::once())
            ->method('preProcessValueHash')
            ->with([1, 2, 3])
            ->willReturn([4, 5, 6]);

        $fieldTypeMock = $this->fieldTypeMock;
        $this->fieldTypeServiceMock->expects(self::once())
            ->method('getFieldType')
            ->with(self::equalTo('some-fancy-field-type'))
            ->willReturnCallback(
                // Avoid PHPUnit cloning
                static function () use ($fieldTypeMock) {
                    return $fieldTypeMock;
                }
            );

        $fieldTypeMock->expects(self::once())
            ->method('fromHash')
            ->with(self::equalTo([4, 5, 6]))
            ->willReturn(['foo', 'bar']);

        self::assertEquals(
            ['foo', 'bar'],
            $fieldTypeParser->parseValue(
                'some-fancy-field-type',
                [1, 2, 3]
            )
        );
    }

    public function testParseFieldSettings(): void
    {
        $fieldTypeParser = $this->getFieldTypeParser();

        $fieldTypeMock = $this->fieldTypeMock;
        $this->fieldTypeServiceMock->expects(self::once())
            ->method('getFieldType')
            ->with(self::equalTo('some-fancy-field-type'))
            ->willReturnCallback(
                // Avoid PHPUnit cloning
                static function () use ($fieldTypeMock) {
                    return $fieldTypeMock;
                }
            );

        $fieldTypeMock->expects(self::once())
            ->method('fieldSettingsFromHash')
            ->with(self::equalTo([1, 2, 3]))
            ->willReturn(['foo', 'bar']);

        self::assertEquals(
            ['foo', 'bar'],
            $fieldTypeParser->parseFieldSettings(
                'some-fancy-field-type',
                [1, 2, 3]
            )
        );
    }

    public function testParseFieldSettingsWithPreProcessing(): void
    {
        $fieldTypeParser = $this->getFieldTypeParser();

        $this->fieldTypeProcessorRegistryMock->expects(self::once())
            ->method('hasProcessor')
            ->with(self::equalTo('some-fancy-field-type'))
            ->willReturn(true);

        $processor = $this->fieldTypeProcessorMock;
        $this->fieldTypeProcessorRegistryMock->expects(self::once())
            ->method('getProcessor')
            ->with(self::equalTo('some-fancy-field-type'))
            ->willReturnCallback(
                static function () use ($processor) {
                    return $processor;
                }
            );

        $processor->expects(self::once())
            ->method('preProcessFieldSettingsHash')
            ->with([1, 2, 3])
            ->willReturn([4, 5, 6]);

        $fieldTypeMock = $this->fieldTypeMock;
        $this->fieldTypeServiceMock->expects(self::once())
            ->method('getFieldType')
            ->with(self::equalTo('some-fancy-field-type'))
            ->willReturnCallback(
                // Avoid PHPUnit cloning
                static function () use ($fieldTypeMock) {
                    return $fieldTypeMock;
                }
            );

        $fieldTypeMock->expects(self::once())
            ->method('fieldSettingsFromHash')
            ->with(self::equalTo([4, 5, 6]))
            ->willReturn(['foo', 'bar']);

        self::assertEquals(
            ['foo', 'bar'],
            $fieldTypeParser->parseFieldSettings(
                'some-fancy-field-type',
                [1, 2, 3]
            )
        );
    }

    public function testParseValidatorConfiguration(): void
    {
        $fieldTypeParser = $this->getFieldTypeParser();

        $fieldTypeMock = $this->fieldTypeMock;
        $this->fieldTypeServiceMock->expects(self::once())
            ->method('getFieldType')
            ->with(self::equalTo('some-fancy-field-type'))
            ->willReturnCallback(
                // Avoid PHPUnit cloning
                static function () use ($fieldTypeMock) {
                    return $fieldTypeMock;
                }
            );

        $fieldTypeMock->expects(self::once())
            ->method('validatorConfigurationFromHash')
            ->with(self::equalTo([1, 2, 3]))
            ->willReturn(['foo', 'bar']);

        self::assertEquals(
            ['foo', 'bar'],
            $fieldTypeParser->parseValidatorConfiguration(
                'some-fancy-field-type',
                [1, 2, 3]
            )
        );
    }

    public function testParseValidatorConfigurationWithPreProcessing(): void
    {
        $fieldTypeParser = $this->getFieldTypeParser();

        $this->fieldTypeProcessorRegistryMock->expects(self::once())
            ->method('hasProcessor')
            ->with(self::equalTo('some-fancy-field-type'))
            ->willReturn(true);

        $processor = $this->fieldTypeProcessorMock;
        $this->fieldTypeProcessorRegistryMock->expects(self::once())
            ->method('getProcessor')
            ->with(self::equalTo('some-fancy-field-type'))
            ->willReturnCallback(
                static function () use ($processor) {
                    return $processor;
                }
            );

        $processor->expects(self::once())
            ->method('preProcessValidatorConfigurationHash')
            ->with([1, 2, 3])
            ->willReturn([4, 5, 6]);

        $fieldTypeMock = $this->fieldTypeMock;
        $this->fieldTypeServiceMock->expects(self::once())
            ->method('getFieldType')
            ->with(self::equalTo('some-fancy-field-type'))
            ->willReturnCallback(
                // Avoid PHPUnit cloning
                static function () use ($fieldTypeMock) {
                    return $fieldTypeMock;
                }
            );

        $fieldTypeMock->expects(self::once())
            ->method('validatorConfigurationFromHash')
            ->with(self::equalTo([4, 5, 6]))
            ->willReturn(['foo', 'bar']);

        self::assertEquals(
            ['foo', 'bar'],
            $fieldTypeParser->parseValidatorConfiguration(
                'some-fancy-field-type',
                [1, 2, 3]
            )
        );
    }

    protected function getFieldTypeParser(): FieldTypeParser
    {
        return new FieldTypeParser(
            $this->contentServiceMock,
            $this->contentTypeServiceMock,
            $this->fieldTypeServiceMock,
            $this->fieldTypeProcessorRegistryMock
        );
    }
}
