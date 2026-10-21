<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser\Criterion;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\RemoteId as ContentRemoteIdCriterion;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

/**
 * Parser for RemoteId Criterion.
 */
class ContentRemoteId extends BaseParser
{
    /**
     * Parses input structure to a Criterion object.
     *
     * @throws Parser
     */
    public function parse(
        array $data,
        ParsingDispatcher $parsingDispatcher
    ): ContentRemoteIdCriterion {
        if (!array_key_exists('ContentRemoteIdCriterion', $data)) {
            throw new Parser('Invalid <ContentRemoteIdCriterion> format');
        }

        return new ContentRemoteIdCriterion(explode(',', $data['ContentRemoteIdCriterion']));
    }
}
