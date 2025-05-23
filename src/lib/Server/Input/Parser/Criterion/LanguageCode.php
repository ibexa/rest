<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser\Criterion;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LanguageCode as LanguageCodeCriterion;
use Ibexa\Contracts\Rest\Exceptions;
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
     * @param \Ibexa\Contracts\Rest\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\Parser
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LanguageCode
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): LanguageCodeCriterion
    {
        if (!array_key_exists('LanguageCodeCriterion', $data)) {
            throw new Exceptions\Parser('Invalid <LanguageCodeCriterion> format');
        }

        return new LanguageCodeCriterion(explode(',', $data['LanguageCodeCriterion']));
    }
}
