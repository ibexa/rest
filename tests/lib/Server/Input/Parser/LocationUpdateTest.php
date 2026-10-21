<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationUpdateStruct;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Core\Repository\LocationService;
use Ibexa\Rest\Server\Input\Parser\LocationUpdate;
use Ibexa\Rest\Server\Values\RestLocationUpdateStruct;
use PHPUnit\Framework\MockObject\Stub;

class LocationUpdateTest extends BaseTestCase
{
    public function testParse(): void
    {
        $inputArray = [
            'priority' => 0,
            'remoteId' => 'remote-id',
            'hidden' => 'true',
            'sortField' => 'PATH',
            'sortOrder' => 'ASC',
        ];

        $locationUpdate = $this->getParser();
        $result = $locationUpdate->parse($inputArray, $this->getParsingDispatcherStub());

        self::assertInstanceOf(
            RestLocationUpdateStruct::class,
            $result,
            'LocationUpdateStruct not created correctly.'
        );

        self::assertEquals(
            0,
            $result->locationUpdateStruct->priority,
            'LocationUpdateStruct priority property not created correctly.'
        );

        self::assertEquals(
            'remote-id',
            $result->locationUpdateStruct->remoteId,
            'LocationUpdateStruct remoteId property not created correctly.'
        );

        self::assertTrue(
            $result->hidden,
            'hidden property not created correctly.'
        );

        self::assertEquals(
            Location::SORT_FIELD_PATH,
            $result->locationUpdateStruct->sortField,
            'LocationUpdateStruct sortField property not created correctly.'
        );

        self::assertEquals(
            Location::SORT_ORDER_ASC,
            $result->locationUpdateStruct->sortOrder,
            'LocationUpdateStruct sortOrder property not created correctly.'
        );
    }

    public function testParseWithMissingSortField(): void
    {
        $inputArray = [
            'priority' => 0,
            'remoteId' => 'remote-id',
            'sortOrder' => 'ASC',
        ];

        $locationUpdate = $this->getParser();
        $result = $locationUpdate->parse($inputArray, $this->getParsingDispatcherStub());

        self::assertInstanceOf(
            RestLocationUpdateStruct::class,
            $result
        );

        self::assertNull(
            $result->locationUpdateStruct->sortField
        );
    }

    /**
     * Test LocationUpdate parser with missing sort order.
     */
    public function testParseWithMissingSortOrder(): void
    {
        $inputArray = [
            'priority' => 0,
            'remoteId' => 'remote-id',
            'sortField' => 'PATH',
        ];

        $locationUpdate = $this->getParser();
        $result = $locationUpdate->parse($inputArray, $this->getParsingDispatcherStub());

        self::assertInstanceOf(
            RestLocationUpdateStruct::class,
            $result
        );

        self::assertNull(
            $result->locationUpdateStruct->sortOrder
        );
    }

    protected function internalGetParser(): LocationUpdate
    {
        return new LocationUpdate(
            $this->getLocationServiceMock(),
            $this->getParserTools()
        );
    }

    protected function getLocationServiceMock(): LocationService & Stub
    {
        $locationServiceMock = self::createStub(LocationService::class);

        $locationServiceMock
            ->method('newLocationUpdateStruct')
            ->willReturn(
                new LocationUpdateStruct()
            );

        return $locationServiceMock;
    }
}
