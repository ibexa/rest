<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser\Criterion;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Subtree as SubtreeCriterion;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

/**
 * Parser for Subtree Criterion.
 */
class Subtree extends BaseParser
{
    /**
     * Parses input structure to a Criterion object.
     *
     * @param array $data
     * @param ParsingDispatcher $parsingDispatcher
     *
     * @throws Parser
     *
     * @return SubtreeCriterion
     */
    public function parse(
        array $data,
        ParsingDispatcher $parsingDispatcher
    ) {
        if (!array_key_exists('SubtreeCriterion', $data)) {
            throw new Parser('Invalid <SubtreeCriterion> format');
        }

        return new SubtreeCriterion(explode(',', $data['SubtreeCriterion']));
    }
}

class_alias(Subtree::class, 'EzSystems\EzPlatformRest\Server\Input\Parser\Criterion\Subtree');
