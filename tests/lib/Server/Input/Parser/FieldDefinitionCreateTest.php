<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Core\Repository\ContentTypeService;
use Ibexa\Rest\Input\FieldTypeParser;
use Ibexa\Rest\Server\Input\Parser\FieldDefinitionCreate;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @todo Test with fieldSettings and validatorConfiguration when specified
 */
class FieldDefinitionCreateTest extends BaseTest
{
    public function testParse(): void
    {
        $inputArray = $this->getInputArray();

        $fieldDefinitionCreate = $this->getParser();
        $result = $fieldDefinitionCreate->parse($inputArray, $this->getParsingDispatcherMock());

        self::assertInstanceOf(
            FieldDefinitionCreateStruct::class,
            $result,
            'FieldDefinitionCreateStruct not created correctly.'
        );

        self::assertEquals(
            'title',
            $result->identifier,
            'identifier not created correctly'
        );

        self::assertEquals(
            'ibexa_string',
            $result->fieldTypeIdentifier,
            'fieldTypeIdentifier not created correctly'
        );

        self::assertEquals(
            'content',
            $result->fieldGroup,
            'fieldGroup not created correctly'
        );

        self::assertEquals(
            1,
            $result->position,
            'position not created correctly'
        );

        self::assertTrue(
            $result->isTranslatable,
            'isTranslatable not created correctly'
        );

        self::assertTrue(
            $result->isRequired,
            'isRequired not created correctly'
        );

        self::assertTrue(
            $result->isInfoCollector,
            'isInfoCollector not created correctly'
        );

        self::assertTrue(
            $result->isSearchable,
            'isSearchable not created correctly'
        );

        self::assertEquals(
            'New title',
            $result->defaultValue,
            'defaultValue not created correctly'
        );

        self::assertEquals(
            ['eng-US' => 'Title'],
            $result->names,
            'names not created correctly'
        );

        self::assertEquals(
            ['eng-US' => 'This is the title'],
            $result->descriptions,
            'descriptions not created correctly'
        );

        self::assertEquals(
            ['textRows' => 24],
            $result->fieldSettings,
            'fieldSettings not created correctly'
        );

        self::assertEquals(
            [
                'StringLengthValidator' => [
                    'minStringLength' => 12,
                    'maxStringLength' => 24,
                ],
            ],
            $result->validatorConfiguration,
            'validatorConfiguration not created correctly'
        );
    }

    public function testParseExceptionOnMissingIdentifier(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'identifier\' element for FieldDefinitionCreate.');
        $inputArray = $this->getInputArray();
        unset($inputArray['identifier']);

        $fieldDefinitionCreate = $this->getParser();
        $fieldDefinitionCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnMissingFieldType(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'fieldType\' element for FieldDefinitionCreate.');
        $inputArray = $this->getInputArray();
        unset($inputArray['fieldType']);

        $fieldDefinitionCreate = $this->getParser();
        $fieldDefinitionCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnInvalidNames(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Invalid \'names\' element for FieldDefinitionCreate.');
        $inputArray = $this->getInputArray();
        unset($inputArray['names']['value']);

        $fieldDefinitionCreate = $this->getParser();
        $fieldDefinitionCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnInvalidDescriptions(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Invalid \'descriptions\' element for FieldDefinitionCreate.');
        $inputArray = $this->getInputArray();
        unset($inputArray['descriptions']['value']);

        $fieldDefinitionCreate = $this->getParser();
        $fieldDefinitionCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    protected function internalGetParser(): FieldDefinitionCreate
    {
        return new FieldDefinitionCreate(
            $this->getContentTypeServiceMock(),
            $this->getFieldTypeParserMock(),
            $this->getParserTools()
        );
    }

    protected function getFieldTypeParserMock(): FieldTypeParser&MockObject
    {
        $fieldTypeParserMock = $this->createMock(FieldTypeParser::class);

        $fieldTypeParserMock->expects(self::any())
            ->method('parseValue')
            ->willReturn('New title');

        $fieldTypeParserMock->expects(self::any())
            ->method('parseFieldSettings')
            ->willReturn(['textRows' => 24]);

        $fieldTypeParserMock->expects(self::any())
            ->method('parseValidatorConfiguration')
            ->willReturn(
                [
                    'StringLengthValidator' => [
                        'minStringLength' => 12,
                        'maxStringLength' => 24,
                    ],
                ]
            );

        return $fieldTypeParserMock;
    }

    protected function getContentTypeServiceMock(): ContentTypeService&MockObject
    {
        $contentTypeServiceMock = $this->createMock(ContentTypeService::class);

        $contentTypeServiceMock->expects(self::any())
            ->method('newFieldDefinitionCreateStruct')
            ->with(self::equalTo('title'), self::equalTo('ibexa_string'))
            ->willReturn(
                new FieldDefinitionCreateStruct(
                    [
                            'identifier' => 'title',
                            'fieldTypeIdentifier' => 'ibexa_string',
                        ]
                )
            );

        return $contentTypeServiceMock;
    }

    protected function getInputArray(): array
    {
        return [
            'identifier' => 'title',
            'fieldType' => 'ibexa_string',
            'fieldGroup' => 'content',
            'position' => '1',
            'isTranslatable' => 'true',
            'isRequired' => 'true',
            'isInfoCollector' => 'true',
            'isSearchable' => 'true',
            'defaultValue' => 'New title',
            'names' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-US',
                        '#text' => 'Title',
                    ],
                ],
            ],
            'descriptions' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-US',
                        '#text' => 'This is the title',
                    ],
                ],
            ],
            // Note that ibexa_string does not support settings, but that is irrelevant for the test
            'fieldSettings' => [
                'textRows' => 24,
            ],
            'validatorConfiguration' => [
                'StringLengthValidator' => [
                    'minStringLength' => '12',
                    'maxStringLength' => '24',
                ],
            ],
        ];
    }
}
