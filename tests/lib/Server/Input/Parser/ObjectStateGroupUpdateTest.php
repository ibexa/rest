<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Core\Repository\ObjectStateService;
use Ibexa\Rest\Server\Input\Parser\ObjectStateGroupUpdate;
use PHPUnit\Framework\MockObject\MockObject;

class ObjectStateGroupUpdateTest extends BaseTest
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

        $objectStateGroupUpdate = $this->getParser();
        $result = $objectStateGroupUpdate->parse($inputArray, $this->getParsingDispatcherMock());

        self::assertInstanceOf(
            ObjectStateGroupUpdateStruct::class,
            $result,
            'ObjectStateGroupUpdateStruct not created correctly.'
        );

        self::assertEquals(
            'test-group',
            $result->identifier,
            'ObjectStateGroupUpdateStruct identifier property not created correctly.'
        );

        self::assertEquals(
            'eng-GB',
            $result->defaultLanguageCode,
            'ObjectStateGroupUpdateStruct defaultLanguageCode property not created correctly.'
        );

        self::assertEquals(
            ['eng-GB' => 'Test group'],
            $result->names,
            'ObjectStateGroupUpdateStruct names property not created correctly.'
        );

        self::assertEquals(
            ['eng-GB' => 'Test group description'],
            $result->descriptions,
            'ObjectStateGroupUpdateStruct descriptions property not created correctly.'
        );
    }

    public function testParseExceptionOnInvalidNames(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing or invalid \'names\' element for ObjectStateGroupUpdate.');
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

        $objectStateGroupUpdate = $this->getParser();
        $objectStateGroupUpdate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    protected function internalGetParser(): ObjectStateGroupUpdate
    {
        return new ObjectStateGroupUpdate(
            $this->getObjectStateServiceMock(),
            $this->getParserTools()
        );
    }

    protected function getObjectStateServiceMock(): ObjectStateService & MockObject
    {
        $objectStateServiceMock = $this->createMock(ObjectStateService::class);

        $objectStateServiceMock->expects(self::any())
            ->method('newObjectStateGroupUpdateStruct')
            ->willReturn(
                new ObjectStateGroupUpdateStruct()
            );

        return $objectStateServiceMock;
    }
}
