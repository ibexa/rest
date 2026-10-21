<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser\Criterion;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ParentLocationId as ParentLocationIdCriterion;
use Ibexa\Contracts\Rest\Exceptions\Parser;
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
     * @param array $data
     * @param ParsingDispatcher $parsingDispatcher
     *
     * @throws Parser
     *
     * @return ParentLocationIdCriterion
     */
    public function parse(
        array $data,
        ParsingDispatcher $parsingDispatcher
    ) {
        if (!array_key_exists('ParentLocationIdCriterion', $data)) {
            throw new Parser('Invalid <ParentLocationIdCriterion> format');
        }

        return new ParentLocationIdCriterion(explode(',', $data['ParentLocationIdCriterion']));
    }
}

class_alias(ParentLocationId::class, 'EzSystems\EzPlatformRest\Server\Input\Parser\Criterion\ParentLocationId');
