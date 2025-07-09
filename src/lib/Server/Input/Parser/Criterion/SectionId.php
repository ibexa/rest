<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser\Criterion;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\SectionId as SectionIdCriterion;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

/**
 * Parser for SectionId Criterion.
 */
class SectionId extends BaseParser
{
    /**
     * Parses input structure to a SectionId Criterion object.
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\Parser
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): SectionIdCriterion
    {
        if (!array_key_exists('SectionIdCriterion', $data)) {
            throw new Exceptions\Parser('Invalid <SectionIdCriterion> format');
        }

        return new SectionIdCriterion($data['SectionIdCriterion']);
    }
}
