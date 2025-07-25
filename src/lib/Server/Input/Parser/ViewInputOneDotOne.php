<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser;

use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Server\Input\Parser\Criterion as CriterionParser;
use Ibexa\Rest\Server\Values\RestViewInput;

/**
 * Parser for ViewInput 1.1.
 */
class ViewInputOneDotOne extends CriterionParser
{
    /**
     * Parses input structure to a RestViewInput struct.
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\Parser
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): RestViewInput
    {
        $restViewInput = new RestViewInput();

        // identifier
        if (!array_key_exists('identifier', $data)) {
            throw new Exceptions\Parser('Missing <identifier> attribute for <ViewInput>.');
        }
        $restViewInput->identifier = $data['identifier'];

        // language params
        $restViewInput->languageCode = $data['languageCode'] ?? null;
        $restViewInput->useAlwaysAvailable = $data['useAlwaysAvailable'] ?? null;

        // query
        if (array_key_exists('ContentQuery', $data) && is_array($data['ContentQuery'])) {
            $queryData = $data['ContentQuery'];
            $queryMediaType = 'application/vnd.ibexa.api.internal.ContentQuery';
        }

        if (array_key_exists('LocationQuery', $data) && is_array($data['LocationQuery'])) {
            $queryData = $data['LocationQuery'];
            $queryMediaType = 'application/vnd.ibexa.api.internal.LocationQuery';
        }

        if (!isset($queryMediaType) || !isset($queryData)) {
            throw new Exceptions\Parser('Missing <ContentQuery> or <LocationQuery> attribute for <ViewInput>.');
        }

        $restViewInput->query = $parsingDispatcher->parse($queryData, $queryMediaType);

        return $restViewInput;
    }
}
