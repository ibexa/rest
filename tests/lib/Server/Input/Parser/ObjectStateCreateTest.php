<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateCreateStruct;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Core\Repository\ObjectStateService;
use Ibexa\Rest\Server\Input\Parser\ObjectStateCreate;
use PHPUnit\Framework\MockObject\MockObject;

class ObjectStateCreateTest extends BaseTest
{
    /**
     * Tests the ObjectStateCreate parser.
     */
    public function testParse(): void
    {
        $inputArray = [
            'identifier' => 'test-state',
            'priority' => '0',
            'defaultLanguageCode' => 'eng-GB',
            'names' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test state',
                    ],
                ],
            ],
            'descriptions' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test description',
                    ],
                ],
            ],
        ];

        $objectStateCreate = $this->getParser();
        $result = $objectStateCreate->parse($inputArray, $this->getParsingDispatcherMock());

        self::assertInstanceOf(
            ObjectStateCreateStruct::class,
            $result,
            'ObjectStateCreateStruct not created correctly.'
        );

        self::assertEquals(
            'test-state',
            $result->identifier,
            'ObjectStateCreateStruct identifier property not created correctly.'
        );

        self::assertEquals(
            0,
            $result->priority,
            'ObjectStateCreateStruct priority property not created correctly.'
        );

        self::assertEquals(
            'eng-GB',
            $result->defaultLanguageCode,
            'ObjectStateCreateStruct defaultLanguageCode property not created correctly.'
        );

        self::assertEquals(
            ['eng-GB' => 'Test state'],
            $result->names,
            'ObjectStateCreateStruct names property not created correctly.'
        );

        self::assertEquals(
            ['eng-GB' => 'Test description'],
            $result->descriptions,
            'ObjectStateCreateStruct descriptions property not created correctly.'
        );
    }

    /**
     * Test ObjectStateCreate parser throwing exception on missing identifier.
     */
    public function testParseExceptionOnMissingIdentifier(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'identifier\' attribute for ObjectStateCreate.');
        $inputArray = [
            'priority' => '0',
            'defaultLanguageCode' => 'eng-GB',
            'names' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test state',
                    ],
                ],
            ],
            'descriptions' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test description',
                    ],
                ],
            ],
        ];

        $objectStateCreate = $this->getParser();
        $objectStateCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test ObjectStateCreate parser throwing exception on missing priority.
     */
    public function testParseExceptionOnMissingPriority(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'priority\' attribute for ObjectStateCreate.');
        $inputArray = [
            'identifier' => 'test-state',
            'defaultLanguageCode' => 'eng-GB',
            'names' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test state',
                    ],
                ],
            ],
            'descriptions' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test description',
                    ],
                ],
            ],
        ];

        $objectStateCreate = $this->getParser();
        $objectStateCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test ObjectStateCreate parser throwing exception on missing defaultLanguageCode.
     */
    public function testParseExceptionOnMissingDefaultLanguageCode(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'defaultLanguageCode\' attribute for ObjectStateCreate.');
        $inputArray = [
            'identifier' => 'test-state',
            'priority' => '0',
            'names' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test state',
                    ],
                ],
            ],
            'descriptions' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test description',
                    ],
                ],
            ],
        ];

        $objectStateCreate = $this->getParser();
        $objectStateCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test ObjectStateCreate parser throwing exception on missing names.
     */
    public function testParseExceptionOnMissingNames(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing or invalid \'names\' element for ObjectStateCreate.');
        $inputArray = [
            'identifier' => 'test-state',
            'priority' => '0',
            'defaultLanguageCode' => 'eng-GB',
            'descriptions' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test description',
                    ],
                ],
            ],
        ];

        $objectStateCreate = $this->getParser();
        $objectStateCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test ObjectStateCreate parser throwing exception on invalid names structure.
     */
    public function testParseExceptionOnInvalidNames(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing or invalid \'names\' element for ObjectStateCreate.');
        $inputArray = [
            'identifier' => 'test-state',
            'priority' => '0',
            'defaultLanguageCode' => 'eng-GB',
            'names' => [],
            'descriptions' => [
                'value' => [
                    [
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test description',
                    ],
                ],
            ],
        ];

        $objectStateCreate = $this->getParser();
        $objectStateCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Returns the ObjectStateCreate parser.
     *
     * @return \Ibexa\Rest\Server\Input\Parser\ObjectStateCreate
     */
    protected function internalGetParser(): ObjectStateCreate
    {
        return new ObjectStateCreate(
            $this->getObjectStateServiceMock(),
            $this->getParserTools()
        );
    }

    /**
     * Get the object state service mock object.
     *
     * @return \Ibexa\Contracts\Core\Repository\ObjectStateService
     */
    protected function getObjectStateServiceMock(): MockObject
    {
        $objectStateServiceMock = $this->createMock(ObjectStateService::class);

        $objectStateServiceMock->expects(self::any())
            ->method('newObjectStateCreateStruct')
            ->with(self::equalTo('test-state'))
            ->willReturn(
                new ObjectStateCreateStruct(['identifier' => 'test-state'])
            );

        return $objectStateServiceMock;
    }
}
