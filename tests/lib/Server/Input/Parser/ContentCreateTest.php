<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\FieldTypeService;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationCreateStruct;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Core\Repository\ContentService;
use Ibexa\Core\Repository\ContentTypeService;
use Ibexa\Core\Repository\Values\Content\ContentCreateStruct;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinitionCollection;
use Ibexa\Rest\Input\FieldTypeParser;
use Ibexa\Rest\Server\Input\Parser\ContentCreate;
use Ibexa\Rest\Server\Input\Parser\LocationCreate;
use Ibexa\Rest\Server\Values\RestContentCreateStruct;
use PHPUnit\Framework\MockObject\MockObject;

class ContentCreateTest extends BaseTest
{
    public function testParse(): void
    {
        $inputArray = [
            'ContentType' => [
                '_href' => '/content/types/13',
            ],
            'mainLanguageCode' => 'eng-US',
            'LocationCreate' => [],
            'Section' => [
                '_href' => '/content/sections/4',
            ],
            'alwaysAvailable' => 'true',
            'remoteId' => 'remoteId12345678',
            'User' => [
                '_href' => '/user/users/14',
            ],
            'fields' => [
                'field' => [
                    [
                        'fieldDefinitionIdentifier' => 'subject',
                        'fieldValue' => [],
                    ],
                    [
                        'fieldDefinitionIdentifier' => 'author',
                        'fieldValue' => [],
                    ],
                ],
            ],
        ];

        $contentCreate = $this->getParser();
        $result = $contentCreate->parse($inputArray, $this->getParsingDispatcherMock());

        self::assertInstanceOf(
            RestContentCreateStruct::class,
            $result,
            'ContentCreate not created correctly.'
        );

        self::assertEquals(
            13,
            $result->contentCreateStruct->contentType->id,
            'contentType not created correctly'
        );

        self::assertEquals(
            'eng-US',
            $result->contentCreateStruct->mainLanguageCode,
            'mainLanguageCode not created correctly'
        );

        self::assertEquals(
            4,
            $result->contentCreateStruct->sectionId,
            'sectionId not created correctly'
        );

        self::assertTrue(
            $result->contentCreateStruct->alwaysAvailable,
            'alwaysAvailable not created correctly'
        );

        self::assertEquals(
            'remoteId12345678',
            $result->contentCreateStruct->remoteId,
            'remoteId not created correctly'
        );

        self::assertEquals(
            14,
            $result->contentCreateStruct->ownerId,
            'ownerId not created correctly'
        );

        foreach ($result->contentCreateStruct->fields as $field) {
            self::assertEquals(
                'foo',
                $field->value,
                'field value not created correctly'
            );
        }
    }

    public function testParseExceptionOnMissingLocationCreate(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing or invalid \'LocationCreate\' element for ContentCreate.');
        $inputArray = [
            'ContentType' => [
                '_href' => '/content/types/13',
            ],
            'mainLanguageCode' => 'eng-US',
            'Section' => [
                '_href' => '/content/sections/4',
            ],
            'alwaysAvailable' => 'true',
            'remoteId' => 'remoteId12345678',
            'User' => [
                '_href' => '/user/users/14',
            ],
            'fields' => [
                'field' => [
                    [
                        'fieldDefinitionIdentifier' => 'subject',
                        'fieldValue' => [],
                    ],
                    [
                        'fieldDefinitionIdentifier' => 'author',
                        'fieldValue' => [],
                    ],
                ],
            ],
        ];

        $contentCreate = $this->getParser();
        $contentCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnMissingContentType(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing or invalid \'ContentType\' element for ContentCreate.');
        $inputArray = [
            'mainLanguageCode' => 'eng-US',
            'LocationCreate' => [],
            'Section' => [
                '_href' => '/content/sections/4',
            ],
            'alwaysAvailable' => 'true',
            'remoteId' => 'remoteId12345678',
            'User' => [
                '_href' => '/user/users/14',
            ],
            'fields' => [
                'field' => [
                    [
                        'fieldDefinitionIdentifier' => 'subject',
                        'fieldValue' => [],
                    ],
                    [
                        'fieldDefinitionIdentifier' => 'author',
                        'fieldValue' => [],
                    ],
                ],
            ],
        ];

        $contentCreate = $this->getParser();
        $contentCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnInvalidContentType(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'_href\' attribute for the ContentType element in ContentCreate.');
        $inputArray = [
            'ContentType' => [],
            'mainLanguageCode' => 'eng-US',
            'LocationCreate' => [],
            'Section' => [
                '_href' => '/content/sections/4',
            ],
            'alwaysAvailable' => 'true',
            'remoteId' => 'remoteId12345678',
            'User' => [
                '_href' => '/user/users/14',
            ],
            'fields' => [
                'field' => [
                    [
                        'fieldDefinitionIdentifier' => 'subject',
                        'fieldValue' => [],
                    ],
                    [
                        'fieldDefinitionIdentifier' => 'author',
                        'fieldValue' => [],
                    ],
                ],
            ],
        ];

        $contentCreate = $this->getParser();
        $contentCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnMissingMainLanguageCode(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'mainLanguageCode\' element for ContentCreate.');
        $inputArray = [
            'ContentType' => [
                '_href' => '/content/types/13',
            ],
            'LocationCreate' => [],
            'Section' => [
                '_href' => '/content/sections/4',
            ],
            'alwaysAvailable' => 'true',
            'remoteId' => 'remoteId12345678',
            'User' => [
                '_href' => '/user/users/14',
            ],
            'fields' => [
                'field' => [
                    [
                        'fieldDefinitionIdentifier' => 'subject',
                        'fieldValue' => [],
                    ],
                    [
                        'fieldDefinitionIdentifier' => 'author',
                        'fieldValue' => [],
                    ],
                ],
            ],
        ];

        $contentCreate = $this->getParser();
        $contentCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnInvalidSection(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'_href\' attribute for the Section element in ContentCreate.');
        $inputArray = [
            'ContentType' => [
                '_href' => '/content/types/13',
            ],
            'mainLanguageCode' => 'eng-US',
            'LocationCreate' => [],
            'Section' => [],
            'alwaysAvailable' => 'true',
            'remoteId' => 'remoteId12345678',
            'User' => [
                '_href' => '/user/users/14',
            ],
            'fields' => [
                'field' => [
                    [
                        'fieldDefinitionIdentifier' => 'subject',
                        'fieldValue' => [],
                    ],
                    [
                        'fieldDefinitionIdentifier' => 'author',
                        'fieldValue' => [],
                    ],
                ],
            ],
        ];

        $contentCreate = $this->getParser();
        $contentCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnInvalidUser(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'_href\' attribute for the User element in ContentCreate.');
        $inputArray = [
            'ContentType' => [
                '_href' => '/content/types/13',
            ],
            'mainLanguageCode' => 'eng-US',
            'LocationCreate' => [],
            'Section' => [
                '_href' => '/content/sections/4',
            ],
            'alwaysAvailable' => 'true',
            'remoteId' => 'remoteId12345678',
            'User' => [],
            'fields' => [
                'field' => [
                    [
                        'fieldDefinitionIdentifier' => 'subject',
                        'fieldValue' => [],
                    ],
                    [
                        'fieldDefinitionIdentifier' => 'author',
                        'fieldValue' => [],
                    ],
                ],
            ],
        ];

        $contentCreate = $this->getParser();
        $contentCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnInvalidFields(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing or invalid \'fields\' element for ContentCreate.');
        $inputArray = [
            'ContentType' => [
                '_href' => '/content/types/13',
            ],
            'mainLanguageCode' => 'eng-US',
            'LocationCreate' => [],
            'Section' => [
                '_href' => '/content/sections/4',
            ],
            'alwaysAvailable' => 'true',
            'remoteId' => 'remoteId12345678',
            'User' => [
                '_href' => '/user/users/14',
            ],
        ];

        $contentCreate = $this->getParser();
        $contentCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnMissingFieldDefinitionIdentifier(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'fieldDefinitionIdentifier\' element in Field data for ContentCreate.');
        $inputArray = [
            'ContentType' => [
                '_href' => '/content/types/13',
            ],
            'mainLanguageCode' => 'eng-US',
            'LocationCreate' => [],
            'Section' => [
                '_href' => '/content/sections/4',
            ],
            'alwaysAvailable' => 'true',
            'remoteId' => 'remoteId12345678',
            'User' => [
                '_href' => '/user/users/14',
            ],
            'fields' => [
                'field' => [
                    [
                        'fieldValue' => [],
                    ],
                    [
                        'fieldDefinitionIdentifier' => 'author',
                        'fieldValue' => [],
                    ],
                ],
            ],
        ];

        $contentCreate = $this->getParser();
        $contentCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnInvalidFieldDefinitionIdentifier(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('\'unknown\' is an invalid Field definition identifier for the \'some_class\' content type in ContentCreate.');
        $inputArray = [
            'ContentType' => [
                '_href' => '/content/types/13',
            ],
            'mainLanguageCode' => 'eng-US',
            'LocationCreate' => [],
            'Section' => [
                '_href' => '/content/sections/4',
            ],
            'alwaysAvailable' => 'true',
            'remoteId' => 'remoteId12345678',
            'User' => [
                '_href' => '/user/users/14',
            ],
            'fields' => [
                'field' => [
                    [
                        'fieldDefinitionIdentifier' => 'unknown',
                        'fieldValue' => [],
                    ],
                    [
                        'fieldDefinitionIdentifier' => 'author',
                        'fieldValue' => [],
                    ],
                ],
            ],
        ];

        $contentCreate = $this->getParser();
        $contentCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnMissingFieldValue(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'fieldValue\' element for the \'subject\' identifier in ContentCreate.');
        $inputArray = [
            'ContentType' => [
                '_href' => '/content/types/13',
            ],
            'mainLanguageCode' => 'eng-US',
            'LocationCreate' => [],
            'Section' => [
                '_href' => '/content/sections/4',
            ],
            'alwaysAvailable' => 'true',
            'remoteId' => 'remoteId12345678',
            'User' => [
                '_href' => '/user/users/14',
            ],
            'fields' => [
                'field' => [
                    [
                        'fieldDefinitionIdentifier' => 'subject',
                    ],
                    [
                        'fieldDefinitionIdentifier' => 'author',
                        'fieldValue' => [],
                    ],
                ],
            ],
        ];

        $contentCreate = $this->getParser();
        $contentCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    protected function internalGetParser(): ContentCreate
    {
        return new ContentCreate(
            $this->getContentServiceMock(),
            $this->getContentTypeServiceMock(),
            $this->getFieldTypeParserMock(),
            $this->getLocationCreateParserMock(),
            $this->getParserTools()
        );
    }

    private function getFieldTypeParserMock(): FieldTypeParser & MockObject
    {
        $fieldTypeParserMock = $this->getMockBuilder(FieldTypeParser::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->setConstructorArgs(
                [
                    $this->getContentServiceMock(),
                    $this->getContentTypeServiceMock(),
                    $this->createMock(FieldTypeService::class),
                ]
            )
            ->getMock();

        $fieldTypeParserMock->expects(self::any())
            ->method('parseValue')
            ->with('ibexa_string', [])
            ->willReturn('foo');

        return $fieldTypeParserMock;
    }

    private function getLocationCreateParserMock(): LocationCreate & MockObject
    {
        $locationCreateParserMock = $this->createMock(LocationCreate::class);

        $locationCreateParserMock->expects(self::any())
            ->method('parse')
            ->with([], $this->getParsingDispatcherMock())
            ->willReturn(new LocationCreateStruct());

        return $locationCreateParserMock;
    }

    protected function getContentServiceMock(): ContentService & MockObject
    {
        $contentServiceMock = $this->createMock(ContentService::class);

        $contentType = $this->getContentType();
        $contentServiceMock->expects(self::any())
            ->method('newContentCreateStruct')
            ->with(
                self::equalTo($contentType),
                self::equalTo('eng-US')
            )
            ->willReturn(
                new ContentCreateStruct(
                    [
                        'contentType' => $contentType,
                        'mainLanguageCode' => 'eng-US',
                    ]
                )
            );

        return $contentServiceMock;
    }

    protected function getContentTypeServiceMock(): ContentTypeService & MockObject
    {
        $contentTypeServiceMock = $this->createMock(ContentTypeService::class);

        $contentTypeServiceMock->expects(self::any())
            ->method('loadContentType')
            ->with(self::equalTo(13))
            ->willReturn($this->getContentType());

        return $contentTypeServiceMock;
    }

    public function getParseHrefExpectationsMap(): array
    {
        return [
            ['/content/types/13', 'contentTypeId', 13],
            ['/content/sections/4', 'sectionId', 4],
            ['/user/users/14', 'userId', 14],
        ];
    }

    protected function getContentType(): ContentType
    {
        return new ContentType(
            [
                'id' => 13,
                'identifier' => 'some_class',
                'fieldDefinitions' => new FieldDefinitionCollection([
                    new FieldDefinition(
                        [
                            'id' => 42,
                            'identifier' => 'subject',
                            'fieldTypeIdentifier' => 'ibexa_string',
                        ]
                    ),
                    new FieldDefinition(
                        [
                            'id' => 43,
                            'identifier' => 'author',
                            'fieldTypeIdentifier' => 'ibexa_string',
                        ]
                    ),
                ]),
            ]
        );
    }
}
