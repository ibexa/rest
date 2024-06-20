<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\Rest\Server\Input\Parser\MoveLocation;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class MoveLocationTest extends BaseTest
{
    private const int TESTED_LOCATION_ID = 22;

    private MockObject&LocationService $locationService;

    private ValidatorInterface $validator;

    public function testParse(): void
    {
        $destinationPath = sprintf('/1/2/%d', self::TESTED_LOCATION_ID);

        $inputArray = [
            'destination' => $destinationPath,
        ];

        $moveLocationParser = $this->getParser();

        $this->locationService
            ->expects(self::once())
            ->method('loadLocation')
            ->with(self::TESTED_LOCATION_ID)
            ->willReturn($this->getMockedLocation());

        $result = $moveLocationParser->parse($inputArray, $this->getParsingDispatcherMock());

        self::assertEquals(
            $this->getMockedLocation()->id,
            $result->id,
        );

        self::assertEquals(
            $this->getMockedLocation()->getPathString(),
            $result->getPathString(),
        );
    }

    public function testParseExceptionOnMissingDestinationElement(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage("The 'destination' element for MoveLocationInput is malformed or missing.");

        $inputArray = [
            'new_destination' => '/1/2/3',
        ];

        $sessionInput = $this->getParser();

        $sessionInput->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnInvalidDestinationElement(): void
    {
        $inputArray = [
            'destination' => 'test_destination',
        ];

        $sessionInput = $this->getParser();

        $this->expectException(Parser::class);
        $this->expectExceptionMessage("The 'destination' element for MoveLocationInput is malformed or missing.");

        $sessionInput->parse($inputArray, $this->getParsingDispatcherMock());
    }

    protected function internalGetParser(): MoveLocation
    {
        $locationService = $this->createMock(LocationService::class);
        $this->locationService = $locationService;
        $this->validator = Validation::createValidator();

        return new MoveLocation(
            $this->locationService,
            $this->validator,
        );
    }

    private function getMockedLocation(): Location
    {
        return new Location(
            [
                'id' => self::TESTED_LOCATION_ID,
                'pathString' => sprintf('/1/2/%d', self::TESTED_LOCATION_ID),
            ],
        );
    }
}
