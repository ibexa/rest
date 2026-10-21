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
    protected ContentService & MockObject $contentServiceMock;

    protected ContentTypeService & MockObject $contentTypeServiceMock;

    protected FieldTypeService & MockObject $fieldTypeServiceMock;

    protected ContentType & MockObject $contentTypeMock;

    protected FieldType & MockObject $fieldTypeMock;

    protected FieldTypeProcessorRegistry & MockObject $fieldTypeProcessorRegistryMock;

    protected FieldTypeProcessor & MockObject $fieldTypeProcessorMock;

    public function testParseFieldValue(): void
    {
        $this->getContentServiceMock()->expects($this->once())
            ->method('loadContentInfo')
            ->with('23')
            ->willReturn(
                new ContentInfo(['contentTypeId' => '42'])
            );

        $contentTypeMock = $this->getContentTypeMock();
        $this->getContentTypeServiceMock()->expects($this->once())
            ->method('loadContentType')
            ->with('42')
            ->willReturnCallback(
                // Avoid PHPUnit cloning
                static function () use ($contentTypeMock): ContentType & MockObject {
                    return $contentTypeMock;
                }
            );

        $contentTypeMock->expects($this->once())
            ->method('getFieldDefinition')
            ->with(self::equalTo('my-field-definition'))
            ->willReturn(
                new FieldDefinition(
                    [
                        'fieldTypeIdentifier' => 'some-fancy-field-type',
                    ]
                )
            );

        $this->getFieldTypeProcessorRegistryMock()->expects($this->once())
            ->method('hasProcessor')
            ->with(self::equalTo('some-fancy-field-type'))
            ->willReturn(false);

        $fieldTypeMock = $this->getFieldTypeMock();
        $this->getFieldTypeServiceMock()->expects($this->once())
            ->method('getFieldType')
            ->with(self::equalTo('some-fancy-field-type'))
            ->willReturnCallback(
                // Avoid PHPUnit cloning
                static function () use ($fieldTypeMock): FieldType & MockObject {
                    return $fieldTypeMock;
                }
            );

        $fieldTypeMock->expects($this->once())
            ->method('fromHash')
            ->with(self::equalTo([1, 2, 3]))
            ->willReturn(['foo', 'bar']);

        $fieldTypeParser = $this->getFieldTypeParser();

        self::assertEquals(
            ['foo', 'bar'],
            $fieldTypeParser->parseFieldValue(
                23,
                'my-field-definition',
                [1, 2, 3]
            )
        );
    }

    public function testParseValue(): void
    {
        $this->getFieldTypeProcessorRegistryMock()->expects($this->once())
            ->method('hasProcessor')
            ->with(self::equalTo('some-fancy-field-type'))
            ->willReturn(false);

        $fieldTypeMock = $this->getFieldTypeMock();
        $this->getFieldTypeServiceMock()->expects($this->once())
            ->method('getFieldType')
            ->with(self::equalTo('some-fancy-field-type'))
            ->willReturnCallback(
                // Avoid PHPUnit cloning
                static function () use ($fieldTypeMock): FieldType & MockObject {
                    return $fieldTypeMock;
                }
            );

        $fieldTypeMock->expects($this->once())
            ->method('fromHash')
            ->with(self::equalTo([1, 2, 3]))
            ->willReturn(['foo', 'bar']);

        $fieldTypeParser = $this->getFieldTypeParser();

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
        $this->getFieldTypeProcessorRegistryMock()->expects($this->once())
            ->method('hasProcessor')
            ->with(self::equalTo('some-fancy-field-type'))
            ->willReturn(true);

        $processor = $this->getFieldTypeProcessorMock();
        $this->getFieldTypeProcessorRegistryMock()->expects($this->once())
            ->method('getProcessor')
            ->with(self::equalTo('some-fancy-field-type'))
            ->willReturnCallback(
                static function () use ($processor): FieldTypeProcessor & MockObject {
                    return $processor;
                }
            );

        $processor->expects($this->once())
            ->method('preProcessValueHash')
            ->with([1, 2, 3])
            ->willReturn([4, 5, 6]);

        $fieldTypeMock = $this->getFieldTypeMock();
        $this->getFieldTypeServiceMock()->expects($this->once())
            ->method('getFieldType')
            ->with(self::equalTo('some-fancy-field-type'))
            ->willReturnCallback(
                // Avoid PHPUnit cloning
                static function () use ($fieldTypeMock): FieldType & MockObject {
                    return $fieldTypeMock;
                }
            );

        $fieldTypeMock->expects($this->once())
            ->method('fromHash')
            ->with(self::equalTo([4, 5, 6]))
            ->willReturn(['foo', 'bar']);

        $fieldTypeParser = $this->getFieldTypeParser();

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
        $fieldTypeMock = $this->getFieldTypeMock();
        $this->getFieldTypeServiceMock()->expects($this->once())
            ->method('getFieldType')
            ->with(self::equalTo('some-fancy-field-type'))
            ->willReturnCallback(
                // Avoid PHPUnit cloning
                static function () use ($fieldTypeMock): FieldType & MockObject {
                    return $fieldTypeMock;
                }
            );

        $fieldTypeMock->expects($this->once())
            ->method('fieldSettingsFromHash')
            ->with(self::equalTo([1, 2, 3]))
            ->willReturn(['foo', 'bar']);

        $fieldTypeParser = $this->getFieldTypeParser();

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
        $this->getFieldTypeProcessorRegistryMock()->expects($this->once())
            ->method('hasProcessor')
            ->with(self::equalTo('some-fancy-field-type'))
            ->willReturn(true);

        $processor = $this->getFieldTypeProcessorMock();
        $this->getFieldTypeProcessorRegistryMock()->expects($this->once())
            ->method('getProcessor')
            ->with(self::equalTo('some-fancy-field-type'))
            ->willReturnCallback(
                static function () use ($processor): FieldTypeProcessor & MockObject {
                    return $processor;
                }
            );

        $processor->expects($this->once())
            ->method('preProcessFieldSettingsHash')
            ->with([1, 2, 3])
            ->willReturn([4, 5, 6]);

        $fieldTypeMock = $this->getFieldTypeMock();
        $this->getFieldTypeServiceMock()->expects($this->once())
            ->method('getFieldType')
            ->with(self::equalTo('some-fancy-field-type'))
            ->willReturnCallback(
                // Avoid PHPUnit cloning
                static function () use ($fieldTypeMock): FieldType & MockObject {
                    return $fieldTypeMock;
                }
            );

        $fieldTypeMock->expects($this->once())
            ->method('fieldSettingsFromHash')
            ->with(self::equalTo([4, 5, 6]))
            ->willReturn(['foo', 'bar']);

        $fieldTypeParser = $this->getFieldTypeParser();

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
        $fieldTypeMock = $this->getFieldTypeMock();
        $this->getFieldTypeServiceMock()->expects($this->once())
            ->method('getFieldType')
            ->with(self::equalTo('some-fancy-field-type'))
            ->willReturnCallback(
                // Avoid PHPUnit cloning
                static function () use ($fieldTypeMock): FieldType & MockObject {
                    return $fieldTypeMock;
                }
            );

        $fieldTypeMock->expects($this->once())
            ->method('validatorConfigurationFromHash')
            ->with(self::equalTo([1, 2, 3]))
            ->willReturn(['foo', 'bar']);

        $fieldTypeParser = $this->getFieldTypeParser();

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
        $this->getFieldTypeProcessorRegistryMock()->expects($this->once())
            ->method('hasProcessor')
            ->with(self::equalTo('some-fancy-field-type'))
            ->willReturn(true);

        $processor = $this->getFieldTypeProcessorMock();
        $this->getFieldTypeProcessorRegistryMock()->expects($this->once())
            ->method('getProcessor')
            ->with(self::equalTo('some-fancy-field-type'))
            ->willReturnCallback(
                static function () use ($processor): FieldTypeProcessor & MockObject {
                    return $processor;
                }
            );

        $processor->expects($this->once())
            ->method('preProcessValidatorConfigurationHash')
            ->with([1, 2, 3])
            ->willReturn([4, 5, 6]);

        $fieldTypeMock = $this->getFieldTypeMock();
        $this->getFieldTypeServiceMock()->expects($this->once())
            ->method('getFieldType')
            ->with(self::equalTo('some-fancy-field-type'))
            ->willReturnCallback(
                // Avoid PHPUnit cloning
                static function () use ($fieldTypeMock): FieldType & MockObject {
                    return $fieldTypeMock;
                }
            );

        $fieldTypeMock->expects($this->once())
            ->method('validatorConfigurationFromHash')
            ->with(self::equalTo([4, 5, 6]))
            ->willReturn(['foo', 'bar']);

        $fieldTypeParser = $this->getFieldTypeParser();

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
            $this->contentServiceMock ?? self::createStub(ContentService::class),
            $this->contentTypeServiceMock ?? self::createStub(ContentTypeService::class),
            $this->fieldTypeServiceMock ?? self::createStub(FieldTypeService::class),
            $this->fieldTypeProcessorRegistryMock ?? self::createStub(FieldTypeProcessorRegistry::class)
        );
    }

    private function getContentServiceMock(): ContentService & MockObject
    {
        return $this->contentServiceMock ??= $this->createMock(ContentService::class);
    }

    private function getContentTypeServiceMock(): ContentTypeService & MockObject
    {
        return $this->contentTypeServiceMock ??= $this->createMock(ContentTypeService::class);
    }

    private function getFieldTypeServiceMock(): FieldTypeService & MockObject
    {
        return $this->fieldTypeServiceMock ??= $this->createMock(FieldTypeService::class);
    }

    private function getContentTypeMock(): ContentType & MockObject
    {
        return $this->contentTypeMock ??= $this->createMock(ContentType::class);
    }

    private function getFieldTypeMock(): FieldType & MockObject
    {
        return $this->fieldTypeMock ??= $this->createMock(FieldType::class);
    }

    private function getFieldTypeProcessorRegistryMock(): FieldTypeProcessorRegistry & MockObject
    {
        return $this->fieldTypeProcessorRegistryMock ??= $this->createMock(FieldTypeProcessorRegistry::class);
    }

    private function getFieldTypeProcessorMock(): FieldTypeProcessor & MockObject
    {
        return $this->fieldTypeProcessorMock ??= $this->createMock(FieldTypeProcessor::class);
    }
}
