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
use Ibexa\Rest\Server\Input\Parser\AbstractDestinationLocationParser;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractDestinationLocationInputTest extends BaseTest
{
    private const int TESTED_LOCATION_ID = 22;

    protected MockObject&LocationService $locationService;

    protected ValidatorInterface $validator;

    protected function parse(): void
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

    protected function parseExceptionOnMissingDestinationElement(string $parser): void
    {
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage(
            sprintf('Input data validation failed for %s', $parser),
        );

        $inputArray = [
            'new_destination' => '/1/2/3',
        ];

        $sessionInput = $this->getParser();

        $sessionInput->parse($inputArray, $this->getParsingDispatcherMock());
    }

    protected function parseExceptionOnInvalidDestinationElement(string $parser): void
    {
        $inputArray = [
            'destination' => 'test_destination',
        ];

        $sessionInput = $this->getParser();

        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage(
            sprintf('Input data validation failed for %s', $parser),
        );

        $sessionInput->parse($inputArray, $this->getParsingDispatcherMock());
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

    abstract protected function internalGetParser(): AbstractDestinationLocationParser;
}
