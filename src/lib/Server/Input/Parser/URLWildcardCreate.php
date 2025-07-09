<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser;

use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;
use Ibexa\Rest\Input\ParserTools;

/**
 * Parser for URLWildcardCreate.
 */
class URLWildcardCreate extends BaseParser
{
    protected ParserTools $parserTools;

    public function __construct(ParserTools $parserTools)
    {
        $this->parserTools = $parserTools;
    }

    /**
     * Parse input structure.
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): array
    {
        if (!array_key_exists('sourceUrl', $data)) {
            throw new Exceptions\Parser("Missing 'sourceUrl' value for URLWildcardCreate.");
        }

        if (!array_key_exists('destinationUrl', $data)) {
            throw new Exceptions\Parser("Missing 'destinationUrl' value for URLWildcardCreate.");
        }

        if (!array_key_exists('forward', $data)) {
            throw new Exceptions\Parser("Missing 'forward' value for URLWildcardCreate.");
        }

        $data['forward'] = $this->parserTools->parseBooleanValue($data['forward']);

        return $data;
    }
}
