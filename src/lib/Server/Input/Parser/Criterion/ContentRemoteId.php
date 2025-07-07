<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser\Criterion;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\RemoteId;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\RemoteId as ContentRemoteIdCriterion;
use Ibexa\Contracts\Rest\Exceptions;
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
     * @throws \Ibexa\Contracts\Rest\Exceptions\Parser
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): RemoteId
    {
        if (!array_key_exists('ContentRemoteIdCriterion', $data)) {
            throw new Exceptions\Parser('Invalid <ContentRemoteIdCriterion> format');
        }

        return new ContentRemoteIdCriterion(explode(',', $data['ContentRemoteIdCriterion']));
    }
}
