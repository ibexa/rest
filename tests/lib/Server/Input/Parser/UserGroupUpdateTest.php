<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentMetadataUpdateStruct;
use Ibexa\Contracts\Core\Repository\Values\User\UserGroupUpdateStruct;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Core\Repository\ContentService;
use Ibexa\Core\Repository\ContentTypeService;
use Ibexa\Core\Repository\FieldTypeService;
use Ibexa\Core\Repository\LocationService;
use Ibexa\Core\Repository\UserService;
use Ibexa\Core\Repository\Values\Content\ContentUpdateStruct;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\Rest\Input\FieldTypeParser;
use Ibexa\Rest\Server\Input\Parser\UserGroupUpdate;
use Ibexa\Rest\Server\Values\RestUserGroupUpdateStruct;
use PHPUnit\Framework\MockObject\MockObject;

class UserGroupUpdateTest extends BaseTest
{
    /**
     * Tests the UserGroupUpdate parser.
     */
    public function testParse(): void
    {
        $inputArray = [
            'mainLanguageCode' => 'eng-US',
            'Section' => [
                '_href' => '/content/sections/1',
            ],
            'remoteId' => 'remoteId123456',
            'fields' => [
                'field' => [
                    [
                        'fieldDefinitionIdentifier' => 'name',
                        'fieldValue' => [],
                    ],
                ],
            ],
            '__url' => '/user/groups/1/5',
        ];

        $userGroupUpdate = $this->getParser();
        $result = $userGroupUpdate->parse($inputArray, $this->getParsingDispatcherMock());

        self::assertInstanceOf(
            RestUserGroupUpdateStruct::class,
            $result,
            'UserGroupUpdate not created correctly.'
        );

        self::assertInstanceOf(
            ContentUpdateStruct::class,
            $result->userGroupUpdateStruct->contentUpdateStruct,
            'UserGroupUpdate not created correctly.'
        );

        self::assertInstanceOf(
            ContentMetadataUpdateStruct::class,
            $result->userGroupUpdateStruct->contentMetadataUpdateStruct,
            'UserGroupUpdate not created correctly.'
        );

        self::assertEquals(
            1,
            $result->sectionId,
            'sectionId not created correctly'
        );

        self::assertEquals(
            'eng-US',
            $result->userGroupUpdateStruct->contentMetadataUpdateStruct->mainLanguageCode,
            'mainLanguageCode not created correctly'
        );

        self::assertEquals(
            'remoteId123456',
            $result->userGroupUpdateStruct->contentMetadataUpdateStruct->remoteId,
            'remoteId not created correctly'
        );

        foreach ($result->userGroupUpdateStruct->contentUpdateStruct->fields as $field) {
            self::assertEquals(
                'foo',
                $field->value,
                'field value not created correctly'
            );
        }
    }

    /**
     * Test UserGroupUpdate parser throwing exception on missing Section href.
     */
    public function testParseExceptionOnMissingSectionHref(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'_href\' attribute for the Section element in UserGroupUpdate.');
        $inputArray = [
            'mainLanguageCode' => 'eng-US',
            'Section' => [],
            'remoteId' => 'remoteId123456',
            'fields' => [
                'field' => [
                    [
                        'fieldDefinitionIdentifier' => 'name',
                        'fieldValue' => [],
                    ],
                ],
            ],
            '__url' => '/user/groups/1/5',
        ];

        $userGroupUpdate = $this->getParser();
        $userGroupUpdate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test UserGroupUpdate parser throwing exception on invalid fields data.
     */
    public function testParseExceptionOnInvalidFields(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Invalid \'fields\' element for UserGroupUpdate.');
        $inputArray = [
            'mainLanguageCode' => 'eng-US',
            'Section' => [
                '_href' => '/content/sections/1',
            ],
            'remoteId' => 'remoteId123456',
            'fields' => [],
            '__url' => '/user/groups/1/5',
        ];

        $userGroupUpdate = $this->getParser();
        $userGroupUpdate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test UserGroupUpdate parser throwing exception on missing field definition identifier.
     */
    public function testParseExceptionOnMissingFieldDefinitionIdentifier(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'fieldDefinitionIdentifier\' element in field data for UserGroupUpdate.');
        $inputArray = [
            'mainLanguageCode' => 'eng-US',
            'Section' => [
                '_href' => '/content/sections/1',
            ],
            'remoteId' => 'remoteId123456',
            'fields' => [
                'field' => [
                    [
                        'fieldValue' => [],
                    ],
                ],
            ],
            '__url' => '/user/groups/1/5',
        ];

        $userGroupUpdate = $this->getParser();
        $userGroupUpdate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test UserGroupUpdate parser throwing exception on missing field value.
     */
    public function testParseExceptionOnMissingFieldValue(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'fieldValue\' element for the \'name\' identifier in UserGroupUpdate.');
        $inputArray = [
            'mainLanguageCode' => 'eng-US',
            'Section' => [
                '_href' => '/content/sections/1',
            ],
            'remoteId' => 'remoteId123456',
            'fields' => [
                'field' => [
                    [
                        'fieldDefinitionIdentifier' => 'name',
                    ],
                ],
            ],
            '__url' => '/user/groups/1/5',
        ];

        $userGroupUpdate = $this->getParser();
        $userGroupUpdate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Returns the UserGroupUpdate parser.
     *
     * @return \Ibexa\Rest\Server\Input\Parser\UserGroupUpdate
     */
    protected function internalGetParser(): UserGroupUpdate
    {
        return new UserGroupUpdate(
            $this->getUserServiceMock(),
            $this->getContentServiceMock(),
            $this->getLocationServiceMock(),
            $this->getFieldTypeParserMock()
        );
    }

    /**
     * Get the field type parser mock object.
     *
     * @return \Ibexa\Rest\Input\FieldTypeParser ;
     */
    private function getFieldTypeParserMock(): MockObject
    {
        $fieldTypeParserMock = $this->getMockBuilder(FieldTypeParser::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->setConstructorArgs(
                [
                    $this->getContentServiceMock(),
                    $this->createMock(ContentTypeService::class),
                    $this->createMock(FieldTypeService::class),
                ]
            )
            ->getMock();

        $fieldTypeParserMock->expects(self::any())
            ->method('parseFieldValue')
            ->with(4, 'name', [])
            ->willReturn('foo');

        return $fieldTypeParserMock;
    }

    /**
     * Get the user service mock object.
     *
     * @return \Ibexa\Contracts\Core\Repository\UserService
     */
    protected function getUserServiceMock(): MockObject
    {
        $userServiceMock = $this->createMock(UserService::class);

        $userServiceMock->expects(self::any())
            ->method('newUserGroupUpdateStruct')
            ->willReturn(
                new UserGroupUpdateStruct()
            );

        return $userServiceMock;
    }

    /**
     * Get the location service mock object.
     *
     * @return \Ibexa\Contracts\Core\Repository\LocationService
     */
    protected function getLocationServiceMock(): MockObject
    {
        $userServiceMock = $this->createMock(LocationService::class);

        $userServiceMock->expects(self::any())
            ->method('loadLocation')
            ->with(self::equalTo(5))
            ->willReturn(
                new Location(
                    [
                            'contentInfo' => new ContentInfo(
                                [
                                    'id' => 4,
                                ]
                            ),
                        ]
                )
            );

        return $userServiceMock;
    }

    /**
     * Get the content service mock object.
     *
     * @return \Ibexa\Contracts\Core\Repository\ContentService
     */
    protected function getContentServiceMock(): MockObject
    {
        $contentServiceMock = $this->createMock(ContentService::class);

        $contentServiceMock->expects(self::any())
            ->method('newContentUpdateStruct')
            ->willReturn(
                new ContentUpdateStruct()
            );

        $contentServiceMock->expects(self::any())
            ->method('newContentMetadataUpdateStruct')
            ->willReturn(
                new ContentMetadataUpdateStruct()
            );

        return $contentServiceMock;
    }

    public function getParseHrefExpectationsMap(): array
    {
        return [
            ['/content/sections/1', 'sectionId', 1],
            ['/user/groups/1/5', 'groupPath', '1/5'],
        ];
    }
}
