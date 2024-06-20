<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Rest\Server\Input\Parser\MoveLocation;
use Symfony\Component\Validator\Validation;

final class MoveLocationTest extends AbstractDestinationLocationInputTest
{
    private const string PARSER = 'MoveLocation';

    public function testParse(): void
    {
        $this->parse();
    }

    public function testParseExceptionOnMissingDestinationElement(): void
    {
        $this->parseExceptionOnMissingDestinationElement(self::PARSER);
    }

    public function testParseExceptionOnInvalidDestinationElement(): void
    {
        $this->parseExceptionOnInvalidDestinationElement(self::PARSER);
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
}
