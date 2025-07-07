<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser\Criterion;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LocationId as LocationIdCriterion;
use Ibexa\Contracts\Rest\Exceptions;
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
     * @throws \Ibexa\Contracts\Rest\Exceptions\Parser
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): LocationIdCriterion
    {
        if (!array_key_exists('LocationIdCriterion', $data)) {
            throw new Exceptions\Parser('Invalid <LocationIdCriterion> format');
        }

        return new LocationIdCriterion(explode(',', $data['LocationIdCriterion']));
    }
}
