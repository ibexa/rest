<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser;

use DateTime;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeUpdateStruct;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;
use Ibexa\Rest\Input\ParserTools;

/**
 * Parser for ContentTypeUpdate.
 */
class ContentTypeUpdate extends BaseParser
{
    protected ContentTypeService $contentTypeService;

    protected ParserTools $parserTools;

    public function __construct(ContentTypeService $contentTypeService, ParserTools $parserTools)
    {
        $this->contentTypeService = $contentTypeService;
        $this->parserTools = $parserTools;
    }

    public function parse(array $data, ParsingDispatcher $parsingDispatcher): ContentTypeUpdateStruct
    {
        $contentTypeUpdateStruct = $this->contentTypeService->newContentTypeUpdateStruct();

        if (array_key_exists('identifier', $data)) {
            $contentTypeUpdateStruct->identifier = $data['identifier'];
        }

        if (array_key_exists('mainLanguageCode', $data)) {
            $contentTypeUpdateStruct->mainLanguageCode = $data['mainLanguageCode'];
        }

        if (array_key_exists('remoteId', $data)) {
            $contentTypeUpdateStruct->remoteId = $data['remoteId'];
        }

        if (array_key_exists('urlAliasSchema', $data)) {
            $contentTypeUpdateStruct->urlAliasSchema = $data['urlAliasSchema'];
        }

        if (array_key_exists('nameSchema', $data)) {
            $contentTypeUpdateStruct->nameSchema = $data['nameSchema'];
        }

        if (array_key_exists('isContainer', $data)) {
            $contentTypeUpdateStruct->isContainer = $this->parserTools->parseBooleanValue($data['isContainer']);
        }

        if (array_key_exists('defaultSortField', $data)) {
            $contentTypeUpdateStruct->defaultSortField = $this->parserTools->parseDefaultSortField($data['defaultSortField']);
        }

        if (array_key_exists('defaultSortOrder', $data)) {
            $contentTypeUpdateStruct->defaultSortOrder = $this->parserTools->parseDefaultSortOrder($data['defaultSortOrder']);
        }

        if (array_key_exists('defaultAlwaysAvailable', $data)) {
            $contentTypeUpdateStruct->defaultAlwaysAvailable = $this->parserTools->parseBooleanValue($data['defaultAlwaysAvailable']);
        }

        if (array_key_exists('names', $data)) {
            if (!is_array($data['names']) || !array_key_exists('value', $data['names']) || !is_array($data['names']['value'])) {
                throw new Exceptions\Parser("Invalid 'names' element for ContentTypeUpdate.");
            }

            $contentTypeUpdateStruct->names = $this->parserTools->parseTranslatableList($data['names']);
        }

        if (array_key_exists('descriptions', $data)) {
            if (!is_array($data['descriptions']) || !array_key_exists('value', $data['descriptions']) || !is_array($data['descriptions']['value'])) {
                throw new Exceptions\Parser("Invalid 'descriptions' element for ContentTypeUpdate.");
            }

            $contentTypeUpdateStruct->descriptions = $this->parserTools->parseTranslatableList($data['descriptions']);
        }

        if (array_key_exists('modificationDate', $data)) {
            $contentTypeUpdateStruct->modificationDate = new DateTime($data['modificationDate']);
        }

        if (array_key_exists('User', $data)) {
            if (!array_key_exists('_href', $data['User'])) {
                throw new Exceptions\Parser("Missing '_href' attribute for the User element in ContentTypeUpdate.");
            }

            $contentTypeUpdateStruct->modifierId = (int)$this->uriParser->getAttributeFromUri($data['User']['_href'], 'userId');
        }

        return $contentTypeUpdateStruct;
    }
}
