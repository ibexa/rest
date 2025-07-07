<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;
use Ibexa\Rest\Input\ParserTools;
use Ibexa\Rest\Server\Values\RestLocationUpdateStruct;

/**
 * Parser for LocationUpdate.
 */
class LocationUpdate extends BaseParser
{
    protected LocationService $locationService;

    protected ParserTools $parserTools;

    public function __construct(LocationService $locationService, ParserTools $parserTools)
    {
        $this->locationService = $locationService;
        $this->parserTools = $parserTools;
    }

    /**
     * Parse input structure.
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): RestLocationUpdateStruct
    {
        $locationUpdateStruct = $this->locationService->newLocationUpdateStruct();

        if (array_key_exists('priority', $data)) {
            $locationUpdateStruct->priority = (int)$data['priority'];
        }

        if (array_key_exists('remoteId', $data)) {
            $locationUpdateStruct->remoteId = $data['remoteId'];
        }

        $hidden = null;
        if (array_key_exists('hidden', $data)) {
            $hidden = $this->parserTools->parseBooleanValue($data['hidden']);
        }

        if (array_key_exists('sortField', $data)) {
            $locationUpdateStruct->sortField = $this->parserTools->parseDefaultSortField($data['sortField']);
        }

        if (array_key_exists('sortOrder', $data)) {
            $locationUpdateStruct->sortOrder = $this->parserTools->parseDefaultSortOrder($data['sortOrder']);
        }

        return new RestLocationUpdateStruct($locationUpdateStruct, $hidden);
    }
}
