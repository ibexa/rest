<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\FieldTypeService;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Core\Repository\ContentService;
use Ibexa\Core\Repository\Values\Content\ContentUpdateStruct;
use Ibexa\Rest\Input\FieldTypeParser;
use Ibexa\Rest\Server\Input\Parser\VersionUpdate;
use PHPUnit\Framework\MockObject\MockObject;

class VersionUpdateTest extends BaseTest
{
    public function testParse(): void
    {
        $inputArray = [
            'initialLanguageCode' => 'eng-US',
            'fields' => [
                'field' => [
                    [
                        'fieldDefinitionIdentifier' => 'subject',
                        'fieldValue' => [],
                    ],
                ],
            ],
            '__url' => '/content/objects/42/versions/1',
        ];

        $VersionUpdate = $this->getParser();
        $result = $VersionUpdate->parse($inputArray, $this->getParsingDispatcherMock());

        self::assertInstanceOf(
            ContentUpdateStruct::class,
            $result,
            'VersionUpdate not created correctly.'
        );

        self::assertEquals(
            'eng-US',
            $result->initialLanguageCode,
            'initialLanguageCode not created correctly'
        );

        foreach ($result->fields as $field) {
            self::assertEquals(
                'foo',
                $field->value,
                'field value not created correctly'
            );
        }
    }

    public function testParseExceptionOnInvalidFields(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Invalid \'fields\' element for VersionUpdate.');
        $inputArray = [
            'initialLanguageCode' => 'eng-US',
            'fields' => [],
            '__url' => '/content/objects/42/versions/1',
        ];

        $VersionUpdate = $this->getParser();
        $VersionUpdate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnMissingFieldDefinitionIdentifier(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'fieldDefinitionIdentifier\' element in Field data for VersionUpdate.');
        $inputArray = [
            'initialLanguageCode' => 'eng-US',
            'fields' => [
                'field' => [
                    [
                        'fieldValue' => [],
                    ],
                ],
            ],
            '__url' => '/content/objects/42/versions/1',
        ];

        $VersionUpdate = $this->getParser();
        $VersionUpdate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnMissingFieldValue(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'fieldValue\' element for the \'subject\' identifier in VersionUpdate.');
        $inputArray = [
            'initialLanguageCode' => 'eng-US',
            'fields' => [
                'field' => [
                    [
                        'fieldDefinitionIdentifier' => 'subject',
                    ],
                ],
            ],
            '__url' => '/content/objects/42/versions/1',
        ];

        $VersionUpdate = $this->getParser();
        $VersionUpdate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    protected function internalGetParser(): VersionUpdate
    {
        return new VersionUpdate(
            $this->getContentServiceMock(),
            $this->getFieldTypeParserMock()
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
            ->with(42, 'subject', [])
            ->willReturn('foo');

        return $fieldTypeParserMock;
    }

    protected function getContentServiceMock(): ContentService & MockObject
    {
        $contentServiceMock = $this->createMock(ContentService::class);

        $contentServiceMock->expects(self::any())
            ->method('newContentUpdateStruct')
            ->willReturn(
                new ContentUpdateStruct()
            );

        return $contentServiceMock;
    }

    /**
     * @return array<int, array<int, int|string>>
     */
    public function getParseHrefExpectationsMap(): array
    {
        return [
            ['/content/objects/42/versions/1', 'contentId', 42],
        ];
    }
}
