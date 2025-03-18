<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationCreateStruct;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;
use Ibexa\Rest\Input\ParserTools;

/**
 * Parser for LocationCreate.
 */
class LocationCreate extends BaseParser
{
    /**
     * Location service.
     */
    protected LocationService $locationService;

    /**
     * Parser tools.
     */
    protected ParserTools $parserTools;

    /**
     * Construct.
     *
     * @param \Ibexa\Contracts\Core\Repository\LocationService $locationService
     * @param \Ibexa\Rest\Input\ParserTools $parserTools
     */
    public function __construct(LocationService $locationService, ParserTools $parserTools)
    {
        $this->locationService = $locationService;
        $this->parserTools = $parserTools;
    }

    public function parse(array $data, ParsingDispatcher $parsingDispatcher): LocationCreateStruct
    {
        if (!array_key_exists('ParentLocation', $data) || !is_array($data['ParentLocation'])) {
            throw new Exceptions\Parser("Missing or invalid 'ParentLocation' element for LocationCreate.");
        }

        if (!array_key_exists('_href', $data['ParentLocation'])) {
            throw new Exceptions\Parser("Missing '_href' attribute for the ParentLocation element in LocationCreate.");
        }

        $locationHrefParts = explode('/', $this->uriParser->getAttributeFromUri($data['ParentLocation']['_href'], 'locationPath'));

        $locationCreateStruct = $this->locationService->newLocationCreateStruct(
            array_pop($locationHrefParts)
        );

        if (array_key_exists('priority', $data)) {
            $locationCreateStruct->priority = (int)$data['priority'];
        }

        if (array_key_exists('hidden', $data)) {
            $locationCreateStruct->hidden = $this->parserTools->parseBooleanValue($data['hidden']);
        }

        if (array_key_exists('remoteId', $data)) {
            $locationCreateStruct->remoteId = $data['remoteId'];
        }

        if (!array_key_exists('sortField', $data)) {
            throw new Exceptions\Parser("Missing 'sortField' element for LocationCreate.");
        }

        $locationCreateStruct->sortField = $this->parserTools->parseDefaultSortField($data['sortField']);

        if (!array_key_exists('sortOrder', $data)) {
            throw new Exceptions\Parser("Missing 'sortOrder' element for LocationCreate.");
        }

        $locationCreateStruct->sortOrder = $this->parserTools->parseDefaultSortOrder($data['sortOrder']);

        return $locationCreateStruct;
    }
}
