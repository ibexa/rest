<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\FieldTypeService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentMetadataUpdateStruct;
use Ibexa\Contracts\Core\Repository\Values\User\UserUpdateStruct;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Core\Repository\ContentService;
use Ibexa\Core\Repository\UserService;
use Ibexa\Core\Repository\Values\Content\ContentUpdateStruct;
use Ibexa\Rest\Input\FieldTypeParser;
use Ibexa\Rest\Server\Input\Parser\UserUpdate;
use Ibexa\Rest\Server\Values\RestUserUpdateStruct;
use PHPUnit\Framework\MockObject\MockObject;

class UserUpdateTest extends BaseTest
{
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
                        'fieldDefinitionIdentifier' => 'first_name',
                        'fieldValue' => [],
                    ],
                ],
            ],
            'email' => 'admin@link.invalid',
            'password' => 'somePassword',
            'enabled' => 'true',
            '__url' => '/user/users/14',
        ];

        $userUpdate = $this->getParser();
        $result = $userUpdate->parse($inputArray, $this->getParsingDispatcherMock());

        self::assertInstanceOf(
            RestUserUpdateStruct::class,
            $result,
            'UserUpdate not created correctly.'
        );

        self::assertInstanceOf(
            ContentUpdateStruct::class,
            $result->userUpdateStruct->contentUpdateStruct,
            'UserUpdate not created correctly.'
        );

        self::assertInstanceOf(
            ContentMetadataUpdateStruct::class,
            $result->userUpdateStruct->contentMetadataUpdateStruct,
            'UserUpdate not created correctly.'
        );

        self::assertEquals(
            1,
            $result->sectionId,
            'sectionId not created correctly'
        );

        self::assertEquals(
            'eng-US',
            $result->userUpdateStruct->contentMetadataUpdateStruct->mainLanguageCode,
            'mainLanguageCode not created correctly'
        );

        self::assertEquals(
            'remoteId123456',
            $result->userUpdateStruct->contentMetadataUpdateStruct->remoteId,
            'remoteId not created correctly'
        );

        self::assertEquals(
            'admin@link.invalid',
            $result->userUpdateStruct->email,
            'email not created correctly'
        );

        self::assertEquals(
            'somePassword',
            $result->userUpdateStruct->password,
            'password not created correctly'
        );

        self::assertTrue(
            $result->userUpdateStruct->enabled,
            'enabled not created correctly'
        );

        foreach ($result->userUpdateStruct->contentUpdateStruct->fields as $field) {
            self::assertEquals(
                'foo',
                $field->value,
                'field value not created correctly'
            );
        }
    }

    public function testParseExceptionOnMissingSectionHref(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'_href\' attribute for the Section element in UserUpdate.');
        $inputArray = [
            'mainLanguageCode' => 'eng-US',
            'Section' => [],
            'remoteId' => 'remoteId123456',
            'fields' => [
                'field' => [
                    [
                        'fieldDefinitionIdentifier' => 'first_name',
                        'fieldValue' => [],
                    ],
                ],
            ],
            'email' => 'admin@link.invalid',
            'password' => 'somePassword',
            'enabled' => 'true',
            '__url' => '/user/users/14',
        ];

        $userUpdate = $this->getParser();
        $userUpdate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnInvalidFields(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Invalid \'fields\' element for UserUpdate.');
        $inputArray = [
            'mainLanguageCode' => 'eng-US',
            'Section' => [
                '_href' => '/content/sections/1',
            ],
            'remoteId' => 'remoteId123456',
            'fields' => [],
            'email' => 'admin@link.invalid',
            'password' => 'somePassword',
            'enabled' => 'true',
            '__url' => '/user/users/14',
        ];

        $userUpdate = $this->getParser();
        $userUpdate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnMissingFieldDefinitionIdentifier(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'fieldDefinitionIdentifier\' element in field data for UserUpdate.');
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
            'email' => 'admin@link.invalid',
            'password' => 'somePassword',
            'enabled' => 'true',
            '__url' => '/user/users/14',
        ];

        $userUpdate = $this->getParser();
        $userUpdate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnMissingFieldValue(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'fieldValue\' element for the \'first_name\' identifier in UserUpdate.');
        $inputArray = [
            'mainLanguageCode' => 'eng-US',
            'Section' => [
                '_href' => '/content/sections/1',
            ],
            'remoteId' => 'remoteId123456',
            'fields' => [
                'field' => [
                    [
                        'fieldDefinitionIdentifier' => 'first_name',
                    ],
                ],
            ],
            'email' => 'admin@link.invalid',
            'password' => 'somePassword',
            'enabled' => 'true',
            '__url' => '/user/users/14',
        ];

        $userUpdate = $this->getParser();
        $userUpdate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    protected function internalGetParser(): UserUpdate
    {
        return new UserUpdate(
            $this->getUserServiceMock(),
            $this->getContentServiceMock(),
            $this->getFieldTypeParserMock(),
            $this->getParserTools()
        );
    }

    private function getFieldTypeParserMock(): FieldTypeParser & MockObject
    {
        $fieldTypeParserMock = $this->getMockBuilder(FieldTypeParser::class)
            ->setMethods([])
            ->disableOriginalConstructor()
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
            ->with(14, 'first_name', [])
            ->willReturn('foo');

        return $fieldTypeParserMock;
    }

    protected function getUserServiceMock(): UserService & MockObject
    {
        $userServiceMock = $this->createMock(UserService::class);

        $userServiceMock->expects(self::any())
            ->method('newUserUpdateStruct')
            ->willReturn(
                new UserUpdateStruct()
            );

        return $userServiceMock;
    }

    protected function getContentServiceMock(): ContentService & MockObject
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

    /**
     * @return array<int, array<int, int|string>>
     */
    public function getParseHrefExpectationsMap(): array
    {
        return [
            ['/user/users/14', 'userId', 14],
            ['/content/sections/1', 'sectionId', 1],
        ];
    }
}
