<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationCreateStruct;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Core\Repository\LocationService;
use Ibexa\Rest\Server\Input\Parser\LocationCreate;
use PHPUnit\Framework\MockObject\MockObject;

class LocationCreateTest extends BaseTest
{
    public function testParse(): void
    {
        $inputArray = [
            'ParentLocation' => [
                '_href' => '/content/locations/1/2/42',
            ],
            'priority' => '2',
            'hidden' => 'true',
            'remoteId' => 'remoteId12345678',
            'sortField' => 'PATH',
            'sortOrder' => 'ASC',
        ];

        $locationCreate = $this->getParser();
        $result = $locationCreate->parse($inputArray, $this->getParsingDispatcherMock());

        self::assertInstanceOf(
            LocationCreateStruct::class,
            $result,
            'LocationCreateStruct not created correctly.'
        );

        self::assertEquals(
            42,
            $result->parentLocationId,
            'LocationCreateStruct parentLocationId property not created correctly.'
        );

        self::assertEquals(
            2,
            $result->priority,
            'LocationCreateStruct priority property not created correctly.'
        );

        self::assertTrue(
            $result->hidden,
            'LocationCreateStruct hidden property not created correctly.'
        );

        self::assertEquals(
            'remoteId12345678',
            $result->remoteId,
            'LocationCreateStruct remoteId property not created correctly.'
        );

        self::assertEquals(
            Location::SORT_FIELD_PATH,
            $result->sortField,
            'LocationCreateStruct sortField property not created correctly.'
        );

        self::assertEquals(
            Location::SORT_ORDER_ASC,
            $result->sortOrder,
            'LocationCreateStruct sortOrder property not created correctly.'
        );
    }

    public function testParseExceptionOnMissingParentLocation(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing or invalid \'ParentLocation\' element for LocationCreate.');
        $inputArray = [
            'priority' => '0',
            'hidden' => 'false',
            'remoteId' => 'remoteId12345678',
            'sortField' => 'PATH',
            'sortOrder' => 'ASC',
        ];

        $locationCreate = $this->getParser();
        $locationCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnMissingHrefAttribute(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'_href\' attribute for the ParentLocation element in LocationCreate.');
        $inputArray = [
            'ParentLocation' => [],
            'priority' => '0',
            'hidden' => 'false',
            'remoteId' => 'remoteId12345678',
            'sortField' => 'PATH',
            'sortOrder' => 'ASC',
        ];

        $locationCreate = $this->getParser();
        $locationCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnMissingSortField(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'sortField\' element for LocationCreate.');
        $inputArray = [
            'ParentLocation' => [
                '_href' => '/content/locations/1/2/42',
            ],
            'priority' => '0',
            'hidden' => 'false',
            'remoteId' => 'remoteId12345678',
            'sortOrder' => 'ASC',
        ];

        $locationCreate = $this->getParser();
        $locationCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnMissingSortOrder(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'sortOrder\' element for LocationCreate.');
        $inputArray = [
            'ParentLocation' => [
                '_href' => '/content/locations/1/2/42',
            ],
            'priority' => '0',
            'hidden' => 'false',
            'remoteId' => 'remoteId12345678',
            'sortField' => 'PATH',
        ];

        $locationCreate = $this->getParser();
        $locationCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    protected function internalGetParser(): LocationCreate
    {
        return new LocationCreate(
            $this->getLocationServiceMock(),
            $this->getParserTools()
        );
    }

    protected function getLocationServiceMock(): LocationService & MockObject
    {
        $locationServiceMock = $this->createMock(LocationService::class);

        $locationServiceMock->expects(self::any())
            ->method('newLocationCreateStruct')
            ->with(self::equalTo(42))
            ->willReturn(
                new LocationCreateStruct(['parentLocationId' => 42])
            );

        return $locationServiceMock;
    }

    public function getParseHrefExpectationsMap(): array
    {
        return [
            ['/content/locations/1/2/42', 'locationPath', '1/2/42'],
        ];
    }
}
