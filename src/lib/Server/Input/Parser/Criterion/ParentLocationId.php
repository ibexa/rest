<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser\Criterion;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ParentLocationId as ParentLocationIdCriterion;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

/**
 * Parser for LocationId Criterion.
 */
class ParentLocationId extends BaseParser
{
    /**
     * Parses input structure to a ParentLocationId Criterion object.
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\Parser
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): ParentLocationIdCriterion
    {
        if (!array_key_exists('ParentLocationIdCriterion', $data)) {
            throw new Exceptions\Parser('Invalid <ParentLocationIdCriterion> format');
        }

        return new ParentLocationIdCriterion(explode(',', $data['ParentLocationIdCriterion']));
    }
}
