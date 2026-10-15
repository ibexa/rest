<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;
use Ibexa\Rest\Input\ParserTools;

/**
 * Parser for ObjectStateGroupUpdate.
 */
class ObjectStateGroupUpdate extends BaseParser
{
    /**
     * Object state service.
     *
     * @var ObjectStateService
     */
    protected $objectStateService;

    /**
     * @var ParserTools
     */
    protected $parserTools;

    /**
     * Construct.
     *
     * @param ObjectStateService $objectStateService
     * @param ParserTools $parserTools
     */
    public function __construct(
        ObjectStateService $objectStateService,
        ParserTools $parserTools
    ) {
        $this->objectStateService = $objectStateService;
        $this->parserTools = $parserTools;
    }

    /**
     * Parse input structure.
     *
     * @param array $data
     * @param ParsingDispatcher $parsingDispatcher
     *
     * @return ObjectStateGroupUpdateStruct
     */
    public function parse(
        array $data,
        ParsingDispatcher $parsingDispatcher
    ) {
        $objectStateGroupUpdateStruct = $this->objectStateService->newObjectStateGroupUpdateStruct();

        if (array_key_exists('identifier', $data)) {
            $objectStateGroupUpdateStruct->identifier = $data['identifier'];
        }

        if (array_key_exists('defaultLanguageCode', $data)) {
            $objectStateGroupUpdateStruct->defaultLanguageCode = $data['defaultLanguageCode'];
        }

        if (array_key_exists('names', $data)) {
            if (!is_array($data['names'])) {
                throw new Exceptions\Parser("Missing or invalid 'names' element for ObjectStateGroupUpdate.");
            }

            if (!array_key_exists('value', $data['names']) || !is_array($data['names']['value'])) {
                throw new Exceptions\Parser("Missing or invalid 'names' element for ObjectStateGroupUpdate.");
            }

            $objectStateGroupUpdateStruct->names = $this->parserTools->parseTranslatableList($data['names']);
        }

        if (array_key_exists('descriptions', $data) && is_array($data['descriptions'])) {
            $objectStateGroupUpdateStruct->descriptions = $this->parserTools->parseTranslatableList($data['descriptions']);
        }

        return $objectStateGroupUpdateStruct;
    }
}

class_alias(ObjectStateGroupUpdate::class, 'EzSystems\EzPlatformRest\Server\Input\Parser\ObjectStateGroupUpdate');
