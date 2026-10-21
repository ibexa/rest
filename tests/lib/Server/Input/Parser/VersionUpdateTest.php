<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser;

use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Core\Repository\ContentService;
use Ibexa\Core\Repository\Values\Content\ContentUpdateStruct;
use Ibexa\Rest\Input\FieldTypeParser;
use Ibexa\Rest\Server\Input\Parser\VersionUpdate;
use PHPUnit\Framework\MockObject\Stub;

class VersionUpdateTest extends BaseTestCase
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
        $result = $VersionUpdate->parse($inputArray, $this->getParsingDispatcherStub());

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
        $VersionUpdate->parse($inputArray, $this->getParsingDispatcherStub());
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
        $VersionUpdate->parse($inputArray, $this->getParsingDispatcherStub());
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
        $VersionUpdate->parse($inputArray, $this->getParsingDispatcherStub());
    }

    protected function internalGetParser(): VersionUpdate
    {
        return new VersionUpdate(
            $this->getContentServiceMock(),
            $this->getFieldTypeParserMock()
        );
    }

    private function getFieldTypeParserMock(): FieldTypeParser & Stub
    {
        $fieldTypeParserMock = self::createStub(FieldTypeParser::class);

        $fieldTypeParserMock
            ->method('parseFieldValue')
            ->willReturnCallback(static function (...$arguments): mixed {
                self::assertEquals([42, 'subject', []], array_slice($arguments, 0, 3));

                return 'foo';
            });

        return $fieldTypeParserMock;
    }

    protected function getContentServiceMock(): ContentService & Stub
    {
        $contentServiceMock = self::createStub(ContentService::class);

        $contentServiceMock
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
