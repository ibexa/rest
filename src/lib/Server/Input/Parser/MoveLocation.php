<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

final class MoveLocation extends BaseParser
{
    public function __construct(
        private readonly LocationService $locationService
    ) {
    }

    /**
     * @phpstan-param array{
     *     'destination': string,
     * } $data
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Rest\Exceptions\Parser
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): Location
    {
        if (!array_key_exists('destination', $data)) {
            throw new Exceptions\Parser("Missing 'destination' element for MoveLocationInput.");
        }

        return $this->getLocationByPath($data['destination']);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    private function getLocationByPath(string $path): Location
    {
        return $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($path)
        );
    }

    /**
     * @throws \Ibexa\Contracts\Rest\Exceptions\Parser
     */
    private function extractLocationIdFromPath(string $path): int
    {
        $pathParts = explode('/', $path);

        $locationId = (int)array_pop($pathParts);

        if ($locationId <= 0) {
            throw new Exceptions\Parser("The 'destination' element for MoveLocationInput is invalid.");
        }

        return $locationId;
    }
}
