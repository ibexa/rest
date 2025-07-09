<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroupCreateStruct;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;
use Ibexa\Rest\Input\ParserTools;

/**
 * Parser for ObjectStateGroupCreate.
 */
class ObjectStateGroupCreate extends BaseParser
{
    protected ObjectStateService $objectStateService;

    protected ParserTools $parserTools;

    public function __construct(ObjectStateService $objectStateService, ParserTools $parserTools)
    {
        $this->objectStateService = $objectStateService;
        $this->parserTools = $parserTools;
    }

    public function parse(array $data, ParsingDispatcher $parsingDispatcher): ObjectStateGroupCreateStruct
    {
        if (!array_key_exists('identifier', $data)) {
            throw new Exceptions\Parser("Missing 'identifier' attribute for ObjectStateGroupCreate.");
        }

        $objectStateGroupCreateStruct = $this->objectStateService->newObjectStateGroupCreateStruct($data['identifier']);

        if (!array_key_exists('defaultLanguageCode', $data)) {
            throw new Exceptions\Parser("Missing 'defaultLanguageCode' attribute for ObjectStateGroupCreate.");
        }

        $objectStateGroupCreateStruct->defaultLanguageCode = $data['defaultLanguageCode'];

        if (!array_key_exists('names', $data) || !is_array($data['names'])) {
            throw new Exceptions\Parser("Missing or invalid 'names' element for ObjectStateGroupCreate.");
        }

        if (!array_key_exists('value', $data['names']) || !is_array($data['names']['value'])) {
            throw new Exceptions\Parser("Missing or invalid 'names' element for ObjectStateGroupCreate.");
        }

        $objectStateGroupCreateStruct->names = $this->parserTools->parseTranslatableList($data['names']);

        if (array_key_exists('descriptions', $data) && is_array($data['descriptions'])) {
            $objectStateGroupCreateStruct->descriptions = $this->parserTools->parseTranslatableList($data['descriptions']);
        }

        return $objectStateGroupCreateStruct;
    }
}
