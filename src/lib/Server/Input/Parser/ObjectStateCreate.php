<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateCreateStruct;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;
use Ibexa\Rest\Input\ParserTools;

/**
 * Parser for ObjectStateCreate.
 */
class ObjectStateCreate extends BaseParser
{
    protected ObjectStateService $objectStateService;

    protected ParserTools $parserTools;

    public function __construct(ObjectStateService $objectStateService, ParserTools $parserTools)
    {
        $this->objectStateService = $objectStateService;
        $this->parserTools = $parserTools;
    }

    public function parse(array $data, ParsingDispatcher $parsingDispatcher): ObjectStateCreateStruct
    {
        if (!array_key_exists('identifier', $data)) {
            throw new Exceptions\Parser("Missing 'identifier' attribute for ObjectStateCreate.");
        }

        $objectStateCreateStruct = $this->objectStateService->newObjectStateCreateStruct($data['identifier']);

        if (!array_key_exists('priority', $data)) {
            throw new Exceptions\Parser("Missing 'priority' attribute for ObjectStateCreate.");
        }

        $objectStateCreateStruct->priority = (int)$data['priority'];

        if (!array_key_exists('defaultLanguageCode', $data)) {
            throw new Exceptions\Parser("Missing 'defaultLanguageCode' attribute for ObjectStateCreate.");
        }

        $objectStateCreateStruct->defaultLanguageCode = $data['defaultLanguageCode'];

        if (!array_key_exists('names', $data) || !is_array($data['names'])) {
            throw new Exceptions\Parser("Missing or invalid 'names' element for ObjectStateCreate.");
        }

        if (!array_key_exists('value', $data['names']) || !is_array($data['names']['value'])) {
            throw new Exceptions\Parser("Missing or invalid 'names' element for ObjectStateCreate.");
        }

        $objectStateCreateStruct->names = $this->parserTools->parseTranslatableList($data['names']);

        // @todo XSD says that descriptions field is mandatory. Does that make sense?
        if (array_key_exists('descriptions', $data) && is_array($data['descriptions'])) {
            $objectStateCreateStruct->descriptions = $this->parserTools->parseTranslatableList($data['descriptions']);
        }

        return $objectStateCreateStruct;
    }
}
