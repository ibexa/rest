<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser\FacetBuilder;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\FacetBuilder\FieldRangeFacetBuilder;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

/**
 * Parser for FieldRange facet builder.
 */
class FieldRangeParser extends BaseParser
{
    /**
     * Parses input structure to a FacetBuilder object.
     *
     * @param array $data
     * @param ParsingDispatcher $parsingDispatcher
     *
     * @throws Parser
     *
     * @return FieldRangeFacetBuilder
     */
    public function parse(
        array $data,
        ParsingDispatcher $parsingDispatcher
    ) {
        throw new Parser('<FieldRange> is not supported yet');
    }
}

class_alias(FieldRangeParser::class, 'EzSystems\EzPlatformRest\Server\Input\Parser\FacetBuilder\FieldRangeParser');
