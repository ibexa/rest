<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser\FacetBuilder;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\FacetBuilder\CriterionFacetBuilder;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

/**
 * Parser for Criterion facet builder.
 */
class CriterionParser extends BaseParser
{
    /**
     * Parses input structure to a FacetBuilder object.
     *
     * @param array $data
     * @param ParsingDispatcher $parsingDispatcher
     *
     * @throws Parser
     *
     * @return CriterionFacetBuilder
     */
    public function parse(
        array $data,
        ParsingDispatcher $parsingDispatcher
    ) {
        if (!array_key_exists('Criterion', $data)) {
            throw new Parser('Invalid <Criterion> format');
        }

        return new CriterionFacetBuilder($data['Criterion']);
    }
}

class_alias(CriterionParser::class, 'EzSystems\EzPlatformRest\Server\Input\Parser\FacetBuilder\CriterionParser');
