<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser;

use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;
use Ibexa\Rest\Server\Values\RestViewInput;

/**
 * Parser for ViewInput.
 */
class ViewInput extends BaseParser
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
        if (!array_key_exists('Query', $data) || !is_array($data['Query'])) {
            throw new Exceptions\Parser('Missing <Query> attribute for <ViewInput>.');
        }

        $restViewInput->query = $parsingDispatcher->parse($data['Query'], 'application/vnd.ibexa.api.internal.ContentQuery');

        return $restViewInput;
    }
}
