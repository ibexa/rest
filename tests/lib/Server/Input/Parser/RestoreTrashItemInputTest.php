<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\Rest\Server\Exceptions\ValidationFailedException;
use Ibexa\Rest\Server\Input\Parser\RestoreTrashItemInput;
use PHPUnit\Framework\MockObject\Stub;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RestoreTrashItemInputTest extends BaseTestCase
{
    private const int TESTED_LOCATION_ID = 22;

    private LocationService & Stub $locationService;

    private ValidatorInterface $validator;

    public function testParse(): void
    {
        $destinationPath = sprintf('/1/2/%d', self::TESTED_LOCATION_ID);

        $inputArray = [
            'destination' => $destinationPath,
        ];

        $moveLocationParser = $this->getParser();

        $loadLocationCalls = 0;
        $this->locationService
            ->method('loadLocation')
            ->willReturnCallback(function (int $locationId) use (&$loadLocationCalls): Location {
                ++$loadLocationCalls;
                self::assertSame(self::TESTED_LOCATION_ID, $locationId);

                return $this->getMockedLocation();
            });

        $result = $moveLocationParser->parse($inputArray, $this->getParsingDispatcherStub());
        self::assertSame(1, $loadLocationCalls);

        self::assertInstanceOf(
            Location::class,
            $result,
        );

        self::assertEquals(
            $this->getMockedLocation()->getId(),
            $result->getId(),
        );

        self::assertEquals(
            $this->getMockedLocation()->getPathString(),
            $result->getPathString(),
        );
    }

    public function testParseWithMissingDestinationElement(): void
    {
        $inputArray = [];

        $sessionInput = $this->getParser();

        $result = $sessionInput->parse($inputArray, $this->getParsingDispatcherStub());

        self::assertEmpty($result);
    }

    public function testParseExceptionOnInvalidDestinationElement(): void
    {
        $inputArray = [
            'destination' => 'test_destination',
        ];

        $sessionInput = $this->getParser();

        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage('Input data validation failed for RestoreTrashItemInput');

        $sessionInput->parse($inputArray, $this->getParsingDispatcherStub());
    }

    protected function internalGetParser(): RestoreTrashItemInput
    {
        $locationService = self::createStub(LocationService::class);
        $this->locationService = $locationService;
        $this->validator = Validation::createValidator();

        return new RestoreTrashItemInput(
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
