<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Rest\Server\Input\Parser\Criterion;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Sibling as SiblingCriterion;
use Ibexa\Contracts\Rest\Exceptions\Parser as ParserExpcetion;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\Rest\Server\Input\Parser\Criterion\Sibling as SiblingParser;
use Ibexa\Tests\Rest\Server\Input\Parser\BaseTestCase;
use PHPUnit\Framework\MockObject\Stub;

final class SiblingTest extends BaseTestCase
{
    private const int EXAMPLE_LOCATION_ID = 54;
    private const int EXAMPLE_PARENT_LOCATION_ID = 2;

    private LocationService & Stub $locationService;

    protected function setUp(): void
    {
        $this->locationService = self::createStub(LocationService::class);
    }

    public function testParse(): void
    {
        $location = new Location([
            'id' => self::EXAMPLE_LOCATION_ID,
            'parentLocationId' => self::EXAMPLE_PARENT_LOCATION_ID,
        ]);

        $loadLocationCalls = 0;
        $this->locationService
            ->method('loadLocation')
            ->willReturnCallback(static function (int $locationId) use (&$loadLocationCalls, $location): Location {
                ++$loadLocationCalls;
                self::assertSame(self::EXAMPLE_LOCATION_ID, $locationId);

                return $location;
            });

        $actual = $this->getParser()->parse([
            'SiblingCriterion' => self::EXAMPLE_LOCATION_ID,
        ], $this->getParsingDispatcherStub());

        self::assertSame(1, $loadLocationCalls);

        $exepected = new SiblingCriterion(
            self::EXAMPLE_LOCATION_ID,
            self::EXAMPLE_PARENT_LOCATION_ID
        );

        self::assertEquals($exepected, $actual);
    }

    public function testParseThrowsParserException(): void
    {
        $this->expectException(ParserExpcetion::class);
        $this->expectExceptionMessage('Invalid <SiblingCriterion> format');

        $this->getParser()->parse([
        /** Missing SiblingCriterion key */
        ], $this->getParsingDispatcherStub());
    }

    protected function internalGetParser(): SiblingParser
    {
        return new SiblingParser($this->locationService);
    }
}
