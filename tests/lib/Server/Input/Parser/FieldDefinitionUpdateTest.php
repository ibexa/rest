<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinitionUpdateStruct;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Core\Repository\ContentTypeService;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values\ContentType\ContentTypeDraft;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinitionCollection;
use Ibexa\Rest\Input\FieldTypeParser;
use Ibexa\Rest\Server\Input\Parser\FieldDefinitionUpdate;

/**
 * @todo Test with fieldSettings and validatorConfiguration when specified
 */
final class FieldDefinitionUpdateTest extends BaseTest
{
    public function testParse(): void
    {
        $inputArray = $this->getInputArray();

        $fieldDefinitionUpdate = $this->getParser();
        $result = $fieldDefinitionUpdate->parse($inputArray, $this->getParsingDispatcherMock());

        self::assertInstanceOf(
            FieldDefinitionUpdateStruct::class,
            $result,
            'FieldDefinitionUpdateStruct not created correctly.'
        );

        self::assertEquals(
            'title',
            $result->identifier,
            'identifier not created correctly'
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

    public function testParseExceptionOnInvalidNames(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Invalid \'names\' element for FieldDefinitionUpdate.');

        $inputArray = $this->getInputArray();

        unset($inputArray['names']['value']);

        $fieldDefinitionUpdate = $this->getParser();
        $fieldDefinitionUpdate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnInvalidDescriptions(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Invalid \'descriptions\' element for FieldDefinitionUpdate.');
        $inputArray = $this->getInputArray();

        unset($inputArray['descriptions']['value']);

        $fieldDefinitionUpdate = $this->getParser();
        $fieldDefinitionUpdate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    protected function internalGetParser(): FieldDefinitionUpdate
    {
        return new FieldDefinitionUpdate(
            $this->getContentTypeServiceMock(),
            $this->getFieldTypeParserMock(),
            $this->getParserTools()
        );
    }

    protected function getFieldTypeParserMock(): FieldTypeParser
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

    protected function getContentTypeServiceMock(): ContentTypeService
    {
        $contentTypeServiceMock = $this->createMock(ContentTypeService::class);

        $contentTypeServiceMock->expects(self::any())
            ->method('newFieldDefinitionUpdateStruct')
            ->willReturn(
                new FieldDefinitionUpdateStruct()
            );

        $contentTypeServiceMock->expects(self::any())
            ->method('loadContentTypeDraft')
            ->with(self::equalTo(42))
            ->willReturn(
                new ContentTypeDraft(
                    [
                            'innerContentType' => new ContentType([
                                'fieldDefinitions' => new FieldDefinitionCollection([
                                    new FieldDefinition(
                                        [
                                            'id' => 24,
                                            'fieldTypeIdentifier' => 'ezstring',
                                            'identifier' => 'foo',
                                        ]
                                    ),
                                ]),
                            ]),
                        ]
                )
            );

        return $contentTypeServiceMock;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getInputArray(): array
    {
        return [
            '__url' => '/content/types/42/draft/fieldDefinitions/24',
            'identifier' => 'title',
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

    /**
     * @return array{
     *   array{string, string, int}
     * }
     */
    public function getParseHrefExpectationsMap(): array
    {
        return [
            ['/content/types/42/draft/fieldDefinitions/24', 'contentTypeId', 42],
            ['/content/types/42/draft/fieldDefinitions/24', 'fieldDefinitionId', 24],
        ];
    }
}
