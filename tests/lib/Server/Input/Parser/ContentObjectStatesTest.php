<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser;

use Ibexa\Rest\Input\Parser\ContentObjectStates;

class ContentObjectStatesTest extends BaseTest
{
    public function testParse(): void
    {
        $inputArray = [
            'ObjectState' => [
                [
                    '_href' => '/content/objectstategroups/42/objectstates/21',
                ],
            ],
        ];

        $objectState = $this->getParser();
        $result = $objectState->parse($inputArray, $this->getParsingDispatcherMock());

        self::assertIsArray($result, 'ContentObjectStates not parsed correctly');

        self::assertNotEmpty(
            $result,
            'ContentObjectStates has no ObjectState elements'
        );

        self::assertInstanceOf(
            '\\Ibexa\\Rest\\Values\\RestObjectState',
            $result[0],
            'ObjectState not created correctly.'
        );

        self::assertInstanceOf(
            '\\Ibexa\\Contracts\\Core\\Repository\\Values\\ObjectState\\ObjectState',
            $result[0]->objectState,
            'Inner ObjectState not created correctly.'
        );

        self::assertEquals(
            21,
            $result[0]->objectState->id,
            'Inner ObjectState id property not created correctly.'
        );

        self::assertEquals(
            42,
            $result[0]->groupId,
            'groupId property not created correctly.'
        );
    }

    public function testParseExceptionOnMissingHref(): void
    {
        $this->expectException('Ibexa\\Contracts\\Rest\\Exceptions\\Parser');
        $this->expectExceptionMessage('Missing \'_href\' attribute for ObjectState.');
        $inputArray = [
            'ObjectState' => [
                [
                    '_href' => '/content/objectstategroups/42/objectstates/21',
                ],
                [],
            ],
        ];

        $objectState = $this->getParser();
        $objectState->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function getParseHrefExpectationsMap(): array
    {
        return [
            ['/content/objectstategroups/42/objectstates/21', 'objectStateId', 21],
            ['/content/objectstategroups/42/objectstates/21', 'objectStateGroupId', 42],
        ];
    }

    protected function internalGetParser(): ContentObjectStates
    {
        return new ContentObjectStates();
    }
}
