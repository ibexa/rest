<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser\Criterion;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LocationId as LocationIdCriterion;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

/**
 * Parser for LocationId Criterion.
 */
class LocationId extends BaseParser
{
    /**
     * Parses input structure to a Criterion object.
     *
     * @param array $data
     * @param ParsingDispatcher $parsingDispatcher
     *
     * @throws Parser
     *
     * @return LocationIdCriterion
     */
    public function parse(
        array $data,
        ParsingDispatcher $parsingDispatcher
    ) {
        if (!array_key_exists('LocationIdCriterion', $data)) {
            throw new Parser('Invalid <LocationIdCriterion> format');
        }

        return new LocationIdCriterion(explode(',', $data['LocationIdCriterion']));
    }
}

class_alias(LocationId::class, 'EzSystems\EzPlatformRest\Server\Input\Parser\Criterion\LocationId');
