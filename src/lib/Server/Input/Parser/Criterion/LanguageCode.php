<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser\Criterion;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LanguageCode as LanguageCodeCriterion;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

/**
 * Parser for LanguageCode Criterion.
 */
class LanguageCode extends BaseParser
{
    /**
     * Parses input structure to a LanguageCode Criterion object.
     *
     * @param array $data
     * @param ParsingDispatcher $parsingDispatcher
     *
     * @throws Parser
     *
     * @return LanguageCodeCriterion
     */
    public function parse(
        array $data,
        ParsingDispatcher $parsingDispatcher
    ) {
        if (!array_key_exists('LanguageCodeCriterion', $data)) {
            throw new Parser('Invalid <LanguageCodeCriterion> format');
        }

        return new LanguageCodeCriterion(explode(',', $data['LanguageCodeCriterion']));
    }
}

class_alias(LanguageCode::class, 'EzSystems\EzPlatformRest\Server\Input\Parser\Criterion\LanguageCode');
