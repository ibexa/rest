<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\FieldTypeService;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Core\Repository\ContentTypeService;
use Ibexa\Core\Repository\UserService;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinitionCollection;
use Ibexa\Core\Repository\Values\User\UserGroupCreateStruct;
use Ibexa\Rest\Input\FieldTypeParser;
use Ibexa\Rest\Server\Input\Parser\UserGroupCreate;
use PHPUnit\Framework\MockObject\MockObject;

class UserGroupCreateTest extends BaseTest
{
    public function testParse(): void
    {
        $inputArray = [
            'ContentType' => [
                '_href' => '/content/types/3',
            ],
            'mainLanguageCode' => 'eng-US',
            'Section' => [
                '_href' => '/content/sections/4',
            ],
            'remoteId' => 'remoteId12345678',
            'fields' => [
                'field' => [
                    [
                        'fieldDefinitionIdentifier' => 'name',
                        'fieldValue' => [],
                    ],
                ],
            ],
        ];

        $userGroupCreate = $this->getParser();
        $result = $userGroupCreate->parse($inputArray, $this->getParsingDispatcherMock());

        self::assertInstanceOf(
            UserGroupCreateStruct::class,
            $result,
            'UserGroupCreateStruct not created correctly.'
        );

        self::assertInstanceOf(
            ContentType::class,
            $result->contentType,
            'contentType not created correctly.'
        );

        self::assertEquals(
            3,
            $result->contentType->id,
            'contentType not created correctly'
        );

        self::assertEquals(
            'eng-US',
            $result->mainLanguageCode,
            'mainLanguageCode not created correctly'
        );

        self::assertEquals(
            4,
            $result->sectionId,
            'sectionId not created correctly'
        );

        self::assertEquals(
            'remoteId12345678',
            $result->remoteId,
            'remoteId not created correctly'
        );

        foreach ($result->fields as $field) {
            self::assertEquals(
                'foo',
                $field->value,
                'field value not created correctly'
            );
        }
    }

    public function testParseExceptionOnInvalidContentType(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'_href\' attribute for the ContentType element in UserGroupCreate.');
        $inputArray = [
            'ContentType' => [],
            'mainLanguageCode' => 'eng-US',
            'Section' => [
                '_href' => '/content/sections/4',
            ],
            'remoteId' => 'remoteId12345678',
            'fields' => [
                'field' => [
                    [
                        'fieldDefinitionIdentifier' => 'name',
                        'fieldValue' => [],
                    ],
                ],
            ],
        ];

        $userGroupCreate = $this->getParser();
        $userGroupCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnMissingMainLanguageCode(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'mainLanguageCode\' element for UserGroupCreate.');
        $inputArray = [
            'ContentType' => [
                '_href' => '/content/types/3',
            ],
            'Section' => [
                '_href' => '/content/sections/4',
            ],
            'remoteId' => 'remoteId12345678',
            'fields' => [
                'field' => [
                    [
                        'fieldDefinitionIdentifier' => 'name',
                        'fieldValue' => [],
                    ],
                ],
            ],
        ];

        $userGroupCreate = $this->getParser();
        $userGroupCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnInvalidSection(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'_href\' attribute for the Section element in UserGroupCreate.');
        $inputArray = [
            'ContentType' => [
                '_href' => '/content/types/3',
            ],
            'mainLanguageCode' => 'eng-US',
            'Section' => [],
            'remoteId' => 'remoteId12345678',
            'fields' => [
                'field' => [
                    [
                        'fieldDefinitionIdentifier' => 'name',
                        'fieldValue' => [],
                    ],
                ],
            ],
        ];

        $userGroupCreate = $this->getParser();
        $userGroupCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnInvalidFields(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing or invalid \'fields\' element for UserGroupCreate.');
        $inputArray = [
            'ContentType' => [
                '_href' => '/content/types/3',
            ],
            'mainLanguageCode' => 'eng-US',
            'Section' => [
                '_href' => '/content/sections/4',
            ],
            'remoteId' => 'remoteId12345678',
        ];

        $userGroupCreate = $this->getParser();
        $userGroupCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnMissingFieldDefinitionIdentifier(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'fieldDefinitionIdentifier\' element in field data for UserGroupCreate.');
        $inputArray = [
            'ContentType' => [
                '_href' => '/content/types/3',
            ],
            'mainLanguageCode' => 'eng-US',
            'Section' => [
                '_href' => '/content/sections/4',
            ],
            'remoteId' => 'remoteId12345678',
            'fields' => [
                'field' => [
                    [
                        'fieldValue' => [],
                    ],
                    [
                        'fieldDefinitionIdentifier' => 'name',
                        'fieldValue' => [],
                    ],
                ],
            ],
        ];

        $userGroupCreate = $this->getParser();
        $userGroupCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnInvalidFieldDefinitionIdentifier(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('\'unknown\' is an invalid Field definition identifier for the \'some_class\' content type in UserGroupCreate.');
        $inputArray = [
            'ContentType' => [
                '_href' => '/content/types/3',
            ],
            'mainLanguageCode' => 'eng-US',
            'Section' => [
                '_href' => '/content/sections/4',
            ],
            'remoteId' => 'remoteId12345678',
            'fields' => [
                'field' => [
                    [
                        'fieldDefinitionIdentifier' => 'unknown',
                        'fieldValue' => [],
                    ],
                ],
            ],
        ];

        $userGroupCreate = $this->getParser();
        $userGroupCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnMissingFieldValue(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'fieldValue\' element for the \'name\' identifier in UserGroupCreate.');
        $inputArray = [
            'ContentType' => [
                '_href' => '/content/types/3',
            ],
            'mainLanguageCode' => 'eng-US',
            'Section' => [
                '_href' => '/content/sections/4',
            ],
            'remoteId' => 'remoteId12345678',
            'fields' => [
                'field' => [
                    [
                        'fieldDefinitionIdentifier' => 'name',
                    ],
                ],
            ],
        ];

        $userGroupCreate = $this->getParser();
        $userGroupCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    protected function internalGetParser(): UserGroupCreate
    {
        return new UserGroupCreate(
            $this->getUserServiceMock(),
            $this->getContentTypeServiceMock(),
            $this->getFieldTypeParserMock()
        );
    }

    private function getFieldTypeParserMock(): FieldTypeParser & MockObject
    {
        $fieldTypeParserMock = $this->getMockBuilder(FieldTypeParser::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->setConstructorArgs(
                [
                    $this->createMock(ContentService::class),
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

    protected function getUserServiceMock(): UserService & MockObject
    {
        $userServiceMock = $this->createMock(UserService::class);

        $contentType = $this->getContentType();
        $userServiceMock->expects(self::any())
            ->method('newUserGroupCreateStruct')
            ->with(
                self::equalTo('eng-US'),
                self::equalTo($contentType)
            )
            ->willReturn(
                new UserGroupCreateStruct(
                    [
                        'contentType' => $contentType,
                        'mainLanguageCode' => 'eng-US',
                    ]
                )
            );

        return $userServiceMock;
    }

    protected function getContentTypeServiceMock(): ContentTypeService & MockObject
    {
        $contentTypeServiceMock = $this->createMock(ContentTypeService::class);

        $contentTypeServiceMock->expects(self::any())
            ->method('loadContentType')
            ->with(self::equalTo(3))
            ->willReturn($this->getContentType());

        return $contentTypeServiceMock;
    }

    protected function getContentType(): ContentType
    {
        return new ContentType(
            [
                'id' => 3,
                'identifier' => 'some_class',
                'fieldDefinitions' => new FieldDefinitionCollection([
                    new FieldDefinition(
                        [
                            'id' => 42,
                            'identifier' => 'name',
                            'fieldTypeIdentifier' => 'ibexa_string',
                        ]
                    ),
                ]),
            ]
        );
    }

    public function getParseHrefExpectationsMap(): array
    {
        return [
            ['/content/types/3', 'contentTypeId', 3],
            ['/content/sections/4', 'sectionId', 4],
        ];
    }
}
