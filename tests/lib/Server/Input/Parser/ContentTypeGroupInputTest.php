<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroupCreateStruct;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Core\Repository\ContentTypeService;
use Ibexa\Rest\Server\Input\Parser\ContentTypeGroupInput;
use PHPUnit\Framework\MockObject\MockObject;

class ContentTypeGroupInputTest extends BaseTest
{
    public function testParse(): void
    {
        $inputArray = [
            'identifier' => 'Identifier Bar',
            'User' => [
                '_href' => '/user/users/14',
            ],
            'modificationDate' => '2012-12-31T12:00:00',
        ];

        $contentTypeGroupInput = $this->getParser();
        $result = $contentTypeGroupInput->parse($inputArray, $this->getParsingDispatcherMock());

        self::assertInstanceOf(
            ContentTypeGroupCreateStruct::class,
            $result,
            'ContentTypeGroupCreateStruct not created correctly.'
        );

        self::assertEquals(
            'Identifier Bar',
            $result->identifier,
            'ContentTypeGroupCreateStruct identifier property not created correctly.'
        );

        self::assertEquals(
            14,
            $result->creatorId,
            'ContentTypeGroupCreateStruct creatorId property not created correctly.'
        );

        self::assertEquals(
            new \DateTime('2012-12-31T12:00:00'),
            $result->creationDate,
            'ContentTypeGroupCreateStruct creationDate property not created correctly.'
        );
    }

    public function testParseExceptionOnInvalidUser(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'_href\' attribute for the User element in ContentTypeGroupInput.');
        $inputArray = [
            'identifier' => 'Identifier Bar',
            'User' => [],
            'modificationDate' => '2012-12-31T12:00:00',
        ];

        $contentTypeGroupInput = $this->getParser();
        $contentTypeGroupInput->parse($inputArray, $this->getParsingDispatcherMock());
    }

    protected function internalGetParser(): ContentTypeGroupInput
    {
        return new ContentTypeGroupInput(
            $this->getContentTypeServiceMock(),
            $this->getParserTools()
        );
    }

    protected function getContentTypeServiceMock(): ContentTypeService & MockObject
    {
        $contentTypeServiceMock = $this->createMock(ContentTypeService::class);

        $contentTypeServiceMock->expects(self::any())
            ->method('newContentTypeGroupCreateStruct')
            ->with(self::equalTo('Identifier Bar'))
            ->willReturn(
                new ContentTypeGroupCreateStruct(['identifier' => 'Identifier Bar'])
            );

        return $contentTypeServiceMock;
    }

    /**
     * @return array<array{0: string, 1: string, 2: int}>
     */
    public function getParseHrefExpectationsMap(): array
    {
        return [
            ['/user/users/14', 'userId', 14],
        ];
    }
}
