<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser\Criterion;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ContentTypeId as ContentTypeIdCriterion;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

/**
 * Parser for ViewInput.
 */
class ContentTypeId extends BaseParser
{
    /**
     * Parses input structure to a Criterion object.
     *
     * @param array $data
     * @param ParsingDispatcher $parsingDispatcher
     *
     * @throws Parser
     *
     * @return ContentTypeIdCriterion
     */
    public function parse(
        array $data,
        ParsingDispatcher $parsingDispatcher
    ) {
        if (!array_key_exists('ContentTypeIdCriterion', $data)) {
            throw new Parser('Invalid <ContentTypeIdCriterion> format');
        }

        return new ContentTypeIdCriterion($data['ContentTypeIdCriterion']);
    }
}

class_alias(ContentTypeId::class, 'EzSystems\EzPlatformRest\Server\Input\Parser\Criterion\ContentTypeId');
