<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Core\Repository\ContentTypeService;
use Ibexa\Core\Repository\Values\ContentType\ContentTypeCreateStruct;
use Ibexa\Rest\Server\Input\Parser\ContentTypeCreate;
use Ibexa\Rest\Server\Input\Parser\FieldDefinitionCreate;
use PHPUnit\Framework\MockObject\MockObject;

class ContentTypeCreateTest extends BaseTest
{
        public function testParse(): void
        {
        $inputArray = $this->getInputArray();

        $contentTypeCreate = $this->getParser();
        $result = $contentTypeCreate->parse($inputArray, $this->getParsingDispatcherMock());

        self::assertInstanceOf(
            ContentTypeCreateStruct::class,
            $result,
            'ContentTypeCreateStruct not created correctly.'
        );

        self::assertEquals(
            'new_content_type',
            $result->identifier,
            'identifier not created correctly'
        );

        self::assertEquals(
            'eng-US',
            $result->mainLanguageCode,
            'mainLanguageCode not created correctly'
        );

        self::assertEquals(
            'remote123456',
            $result->remoteId,
            'remoteId not created correctly'
        );

        self::assertEquals(
            '<title>',
            $result->urlAliasSchema,
            'urlAliasSchema not created correctly'
        );

        self::assertEquals(
            '<title>',
            $result->nameSchema,
            'nameSchema not created correctly'
        );

        self::assertTrue(
            $result->isContainer,
            'isContainer not created correctly'
        );

        self::assertEquals(
            Location::SORT_FIELD_PATH,
            $result->defaultSortField,
            'defaultSortField not created correctly'
        );

        self::assertEquals(
            Location::SORT_ORDER_ASC,
            $result->defaultSortOrder,
            'defaultSortOrder not created correctly'
        );

        self::assertTrue(
            $result->defaultAlwaysAvailable,
            'defaultAlwaysAvailable not created correctly'
        );

        self::assertEquals(
            ['eng-US' => 'New content type'],
            $result->names,
            'names not created correctly'
        );

        self::assertEquals(
            ['eng-US' => 'New content type description'],
            $result->descriptions,
            'descriptions not created correctly'
        );

        self::assertEquals(
            new \DateTime('2012-12-31T12:30:00'),
            $result->creationDate,
            'creationDate not created correctly'
        );

        self::assertEquals(
            14,
            $result->creatorId,
            'creatorId not created correctly'
        );

        foreach ($result->fieldDefinitions as $fieldDefinition) {
            self::assertInstanceOf(
                FieldDefinitionCreateStruct::class,
                $fieldDefinition,
                'ContentTypeCreateStruct field definition not created correctly.'
            );
        }
    }

    public function testParseExceptionOnMissingIdentifier(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'identifier\' element for ContentTypeCreate.');
        $inputArray = $this->getInputArray();
        unset($inputArray['identifier']);

        $contentTypeCreate = $this->getParser();
        $contentTypeCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnMissingMainLanguageCode(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'mainLanguageCode\' element for ContentTypeCreate.');
        $inputArray = $this->getInputArray();
        unset($inputArray['mainLanguageCode']);

        $contentTypeCreate = $this->getParser();
        $contentTypeCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnInvalidNames(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Invalid \'names\' element for ContentTypeCreate.');
        $inputArray = $this->getInputArray();
        unset($inputArray['names']['value']);

        $contentTypeCreate = $this->getParser();
        $contentTypeCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnInvalidDescriptions(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Invalid \'descriptions\' element for ContentTypeCreate.');
        $inputArray = $this->getInputArray();
        unset($inputArray['descriptions']['value']);

        $contentTypeCreate = $this->getParser();
        $contentTypeCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnInvalidUser(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'_href\' attribute for the User element in ContentTypeCreate.');
        $inputArray = $this->getInputArray();
        unset($inputArray['User']['_href']);

        $contentTypeCreate = $this->getParser();
        $contentTypeCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnInvalidFieldDefinitions(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Invalid \'FieldDefinitions\' element for ContentTypeCreate.');
        $inputArray = $this->getInputArray();
        unset($inputArray['FieldDefinitions']['FieldDefinition']);

        $contentTypeCreate = $this->getParser();
        $contentTypeCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnMissingFieldDefinitions(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('ContentTypeCreate should provide at least one Field definition.');
        $inputArray = $this->getInputArray();
        // Field definitions are required only with immediate publish
        $inputArray['__publish'] = true;
        $inputArray['FieldDefinitions']['FieldDefinition'] = [];

        $contentTypeCreate = $this->getParser();
        $contentTypeCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnInvalidFieldDefinition(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Invalid \'FieldDefinition\' element for ContentTypeCreate.');
        $inputArray = $this->getInputArray();
        $inputArray['FieldDefinitions']['FieldDefinition'] = ['hi there'];

        $contentTypeCreate = $this->getParser();
        $contentTypeCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    protected function internalGetParser(): ContentTypeCreate
    {
        return new ContentTypeCreate(
            $this->getContentTypeServiceMock(),
            $this->getFieldDefinitionCreateParserMock(),
            $this->getParserTools()
        );
    }

    private function getFieldDefinitionCreateParserMock(): FieldDefinitionCreate & MockObject
    {
        $fieldDefinitionCreateParserMock = $this->createMock(FieldDefinitionCreate::class);

        $fieldDefinitionCreateParserMock->expects(self::any())
            ->method('parse')
            ->with([], $this->getParsingDispatcherMock())
            ->willReturn(new FieldDefinitionCreateStruct());

        return $fieldDefinitionCreateParserMock;
    }

    protected function getContentTypeServiceMock(): ContentTypeService & MockObject
    {
        $contentTypeServiceMock = $this->createMock(ContentTypeService::class);

        $contentTypeServiceMock->expects(self::any())
            ->method('newContentTypeCreateStruct')
            ->with(self::equalTo('new_content_type'))
            ->willReturn(
                new ContentTypeCreateStruct(
                    [
                            'identifier' => 'new_content_type',
                        ]
                )
            );

        return $contentTypeServiceMock;
    }

    /**
     * Returns the array under test.
     */
    protected function getInputArray(): array
    {
        return [
            'identifier' => 'new_content_type',
            'mainLanguageCode' => 'eng-US',
            'remoteId' => 'remote123456',
            'urlAliasSchema' => '<title>',
            'nameSchema' => '<title>',
            'isContainer' => 'true',
            'defaultSortField' => 'PATH',
            'defaultSortOrder' => 'ASC',
            'defaultAlwaysAvailable' => 'true',
            'names' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-US',
                        '#text' => 'New content type',
                    ],
                ],
            ],
            'descriptions' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-US',
                        '#text' => 'New content type description',
                    ],
                ],
            ],
            'modificationDate' => '2012-12-31T12:30:00',
            'User' => [
                '_href' => '/user/users/14',
            ],
            'FieldDefinitions' => [
                'FieldDefinition' => [
                    [],
                    [],
                ],
            ],
        ];
    }

    public function getParseHrefExpectationsMap(): array
    {
        return [
            ['/user/users/14', 'userId', 14],
        ];
    }
}
