<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeUpdateStruct;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Core\Repository\ContentTypeService;
use Ibexa\Rest\Server\Input\Parser\ContentTypeUpdate;

class ContentTypeUpdateTest extends BaseTest
{
    /**
     * Tests the ContentTypeUpdate parser.
     */
    public function testParse()
    {
        $inputArray = $this->getInputArray();

        $contentTypeUpdate = $this->getParser();
        $result = $contentTypeUpdate->parse($inputArray, $this->getParsingDispatcherMock());

        self::assertInstanceOf(
            ContentTypeUpdateStruct::class,
            $result,
            'ContentTypeUpdateStruct not created correctly.'
        );

        self::assertEquals(
            'updated_content_type',
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
            ['eng-US' => 'Updated content type'],
            $result->names,
            'names not created correctly'
        );

        self::assertEquals(
            ['eng-US' => 'Updated content type description'],
            $result->descriptions,
            'descriptions not created correctly'
        );

        self::assertEquals(
            new \DateTime('2012-12-31T12:30:00'),
            $result->modificationDate,
            'creationDate not created correctly'
        );

        self::assertEquals(
            14,
            $result->modifierId,
            'creatorId not created correctly'
        );
    }

    /**
     * Test ContentTypeUpdate parser throwing exception on invalid names.
     */
    public function testParseExceptionOnInvalidNames()
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Invalid \'names\' element for ContentTypeUpdate.');
        $inputArray = $this->getInputArray();
        unset($inputArray['names']['value']);

        $contentTypeUpdate = $this->getParser();
        $contentTypeUpdate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test ContentTypeUpdate parser throwing exception on invalid descriptions.
     */
    public function testParseExceptionOnInvalidDescriptions()
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Invalid \'descriptions\' element for ContentTypeUpdate.');
        $inputArray = $this->getInputArray();
        unset($inputArray['descriptions']['value']);

        $contentTypeUpdate = $this->getParser();
        $contentTypeUpdate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test ContentTypeUpdate parser throwing exception on invalid User.
     */
    public function testParseExceptionOnInvalidUser()
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'_href\' attribute for the User element in ContentTypeUpdate.');
        $inputArray = $this->getInputArray();
        unset($inputArray['User']['_href']);

        $contentTypeUpdate = $this->getParser();
        $contentTypeUpdate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Returns the ContentTypeUpdate parser.
     *
     * @return \Ibexa\Rest\Server\Input\Parser\ContentTypeUpdate
     */
    protected function internalGetParser()
    {
        return new ContentTypeUpdate(
            $this->getContentTypeServiceMock(),
            $this->getParserTools()
        );
    }

    /**
     * Get the content type service mock object.
     *
     * @return \Ibexa\Contracts\Core\Repository\ContentTypeService
     */
    protected function getContentTypeServiceMock()
    {
        $contentTypeServiceMock = $this->createMock(ContentTypeService::class);

        $contentTypeServiceMock->expects(self::any())
            ->method('newContentTypeUpdateStruct')
            ->willReturn(new ContentTypeUpdateStruct());

        return $contentTypeServiceMock;
    }

    /**
     * Returns the array under test.
     *
     * @return array
     */
    protected function getInputArray()
    {
        return [
            'identifier' => 'updated_content_type',
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
                        '#text' => 'Updated content type',
                    ],
                ],
            ],
            'descriptions' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-US',
                        '#text' => 'Updated content type description',
                    ],
                ],
            ],
            'modificationDate' => '2012-12-31T12:30:00',
            'User' => [
                '_href' => '/user/users/14',
            ],
        ];
    }

    public function getParseHrefExpectationsMap()
    {
        return [
            ['/user/users/14', 'userId', 14],
        ];
    }
}

class_alias(ContentTypeUpdateTest::class, 'EzSystems\EzPlatformRest\Tests\Server\Input\Parser\ContentTypeUpdateTest');
