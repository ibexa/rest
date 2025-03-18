<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateUpdateStruct;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Core\Repository\ObjectStateService;
use Ibexa\Rest\Server\Input\Parser\ObjectStateUpdate;
use PHPUnit\Framework\MockObject\MockObject;

class ObjectStateUpdateTest extends BaseTest
{
    public function testParse(): void
    {
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

        $objectStateUpdate = $this->getParser();
        $result = $objectStateUpdate->parse($inputArray, $this->getParsingDispatcherMock());

        self::assertInstanceOf(
            ObjectStateUpdateStruct::class,
            $result,
            'ObjectStateUpdateStruct not created correctly.'
        );

        self::assertEquals(
            'test-state',
            $result->identifier,
            'ObjectStateUpdateStruct identifier property not created correctly.'
        );

        self::assertEquals(
            'eng-GB',
            $result->defaultLanguageCode,
            'ObjectStateUpdateStruct defaultLanguageCode property not created correctly.'
        );

        self::assertEquals(
            ['eng-GB' => 'Test state'],
            $result->names,
            'ObjectStateUpdateStruct names property not created correctly.'
        );

        self::assertEquals(
            ['eng-GB' => 'Test description'],
            $result->descriptions,
            'ObjectStateUpdateStruct descriptions property not created correctly.'
        );
    }

    public function testParseExceptionOnInvalidNames(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing or invalid \'names\' element for ObjectStateUpdate.');
        $inputArray = [
            'identifier' => 'test-state',
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

        $objectStateUpdate = $this->getParser();
        $objectStateUpdate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    protected function internalGetParser(): ObjectStateUpdate
    {
        return new ObjectStateUpdate(
            $this->getObjectStateServiceMock(),
            $this->getParserTools()
        );
    }

    protected function getObjectStateServiceMock(): ObjectStateService & MockObject
    {
        $objectStateServiceMock = $this->createMock(ObjectStateService::class);

        $objectStateServiceMock->expects(self::any())
            ->method('newObjectStateUpdateStruct')
            ->willReturn(
                new ObjectStateUpdateStruct()
            );

        return $objectStateServiceMock;
    }
}
