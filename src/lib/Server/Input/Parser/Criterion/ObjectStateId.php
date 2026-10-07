<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser\Criterion;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ObjectStateId as ObjectStateIdCriterion;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

/**
 * Parser for ObjectStateId Criterion.
 */
class ObjectStateId extends BaseParser
{
    /**
     * Parses input structure to a ObjectStateId Criterion object.
     *
     * @param array $data
     * @param ParsingDispatcher $parsingDispatcher
     *
     * @throws Parser
     *
     * @return ObjectStateIdCriterion
     */
    public function parse(
        array $data,
        ParsingDispatcher $parsingDispatcher
    ) {
        if (!array_key_exists('ObjectStateIdCriterion', $data)) {
            throw new Parser('Invalid <ObjectStateIdCriterion> format');
        }

        return new ObjectStateIdCriterion(explode(',', $data['ObjectStateIdCriterion']));
    }
}

class_alias(ObjectStateId::class, 'EzSystems\EzPlatformRest\Server\Input\Parser\Criterion\ObjectStateId');
