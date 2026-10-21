<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroupCreateStruct;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Core\Repository\ObjectStateService;
use Ibexa\Rest\Server\Input\Parser\ObjectStateGroupCreate;
use PHPUnit\Framework\MockObject\Stub;

class ObjectStateGroupCreateTest extends BaseTestCase
{
    public function testParse(): void
    {
        $inputArray = [
            'identifier' => 'test-group',
            'defaultLanguageCode' => 'eng-GB',
            'names' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test group',
                    ],
                ],
            ],
            'descriptions' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test group description',
                    ],
                ],
            ],
        ];

        $objectStateGroupCreate = $this->getParser();
        $result = $objectStateGroupCreate->parse($inputArray, $this->getParsingDispatcherStub());

        self::assertInstanceOf(
            ObjectStateGroupCreateStruct::class,
            $result,
            'ObjectStateGroupCreateStruct not created correctly.'
        );

        self::assertEquals(
            'test-group',
            $result->identifier,
            'ObjectStateGroupCreateStruct identifier property not created correctly.'
        );

        self::assertEquals(
            'eng-GB',
            $result->defaultLanguageCode,
            'ObjectStateGroupCreateStruct defaultLanguageCode property not created correctly.'
        );

        self::assertEquals(
            ['eng-GB' => 'Test group'],
            $result->names,
            'ObjectStateGroupCreateStruct names property not created correctly.'
        );

        self::assertEquals(
            ['eng-GB' => 'Test group description'],
            $result->descriptions,
            'ObjectStateGroupCreateStruct descriptions property not created correctly.'
        );
    }

    public function testParseExceptionOnMissingIdentifier(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'identifier\' attribute for ObjectStateGroupCreate.');
        $inputArray = [
            'defaultLanguageCode' => 'eng-GB',
            'names' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test group',
                    ],
                ],
            ],
            'descriptions' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test group description',
                    ],
                ],
            ],
        ];

        $objectStateGroupCreate = $this->getParser();
        $objectStateGroupCreate->parse($inputArray, $this->getParsingDispatcherStub());
    }

    public function testParseExceptionOnMissingDefaultLanguageCode(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'defaultLanguageCode\' attribute for ObjectStateGroupCreate.');
        $inputArray = [
            'identifier' => 'test-group',
            'names' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test group',
                    ],
                ],
            ],
            'descriptions' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test group description',
                    ],
                ],
            ],
        ];

        $objectStateGroupCreate = $this->getParser();
        $objectStateGroupCreate->parse($inputArray, $this->getParsingDispatcherStub());
    }

    public function testParseExceptionOnMissingNames(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing or invalid \'names\' element for ObjectStateGroupCreate.');
        $inputArray = [
            'identifier' => 'test-group',
            'defaultLanguageCode' => 'eng-GB',
            'descriptions' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test group description',
                    ],
                ],
            ],
        ];

        $objectStateGroupCreate = $this->getParser();
        $objectStateGroupCreate->parse($inputArray, $this->getParsingDispatcherStub());
    }

    public function testParseExceptionOnInvalidNames(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing or invalid \'names\' element for ObjectStateGroupCreate.');
        $inputArray = [
            'identifier' => 'test-group',
            'defaultLanguageCode' => 'eng-GB',
            'names' => [],
            'descriptions' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test group description',
                    ],
                ],
            ],
        ];

        $objectStateGroupCreate = $this->getParser();
        $objectStateGroupCreate->parse($inputArray, $this->getParsingDispatcherStub());
    }

    protected function internalGetParser(): ObjectStateGroupCreate
    {
        return new ObjectStateGroupCreate(
            $this->getObjectStateServiceMock(),
            $this->getParserTools()
        );
    }

    protected function getObjectStateServiceMock(): ObjectStateService & Stub
    {
        $objectStateServiceMock = self::createStub(ObjectStateService::class);

        $objectStateServiceMock
            ->method('newObjectStateGroupCreateStruct')
            ->willReturnCallback(static function (...$arguments): ObjectStateGroupCreateStruct {
                self::assertEquals(['test-group'], array_slice($arguments, 0, 1));

                return new ObjectStateGroupCreateStruct(['identifier' => 'test-group']);
            });

        return $objectStateServiceMock;
    }
}
