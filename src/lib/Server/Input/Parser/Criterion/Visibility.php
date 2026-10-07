<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser\Criterion;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Visibility as VisibilityCriterion;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

/**
 * Parser for Visibility Criterion.
 */
class Visibility extends BaseParser
{
    /**
     * Parses input structure to a Visibility Criterion object.
     *
     * @param array $data
     * @param ParsingDispatcher $parsingDispatcher
     *
     * @throws Parser
     *
     * @return VisibilityCriterion
     */
    public function parse(
        array $data,
        ParsingDispatcher $parsingDispatcher
    ) {
        if (!array_key_exists('VisibilityCriterion', $data)) {
            throw new Parser('Invalid <VisibilityCriterion> format');
        }

        if ($data['VisibilityCriterion'] != VisibilityCriterion::VISIBLE && $data['VisibilityCriterion'] != VisibilityCriterion::HIDDEN) {
            throw new Parser('Invalid <VisibilityCriterion> format');
        }

        return new VisibilityCriterion((int)$data['VisibilityCriterion']);
    }
}

class_alias(Visibility::class, 'EzSystems\EzPlatformRest\Server\Input\Parser\Criterion\Visibility');
