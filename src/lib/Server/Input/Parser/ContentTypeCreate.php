<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser;

use DateTime;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeCreateStruct;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;
use Ibexa\Rest\Input\ParserTools;

/**
 * Parser for ContentTypeCreate.
 */
class ContentTypeCreate extends BaseParser
{
    protected ContentTypeService $contentTypeService;

    protected FieldDefinitionCreate $fieldDefinitionCreateParser;

    protected ParserTools $parserTools;

    public function __construct(
        ContentTypeService $contentTypeService,
        FieldDefinitionCreate $fieldDefinitionCreateParser,
        ParserTools $parserTools
    ) {
        $this->contentTypeService = $contentTypeService;
        $this->fieldDefinitionCreateParser = $fieldDefinitionCreateParser;
        $this->parserTools = $parserTools;
    }

    public function parse(array $data, ParsingDispatcher $parsingDispatcher): ContentTypeCreateStruct
    {
        if (!array_key_exists('identifier', $data)) {
            throw new Exceptions\Parser("Missing 'identifier' element for ContentTypeCreate.");
        }

        $contentTypeCreateStruct = $this->contentTypeService->newContentTypeCreateStruct($data['identifier']);

        if (!array_key_exists('mainLanguageCode', $data)) {
            throw new Exceptions\Parser("Missing 'mainLanguageCode' element for ContentTypeCreate.");
        }

        $contentTypeCreateStruct->mainLanguageCode = $data['mainLanguageCode'];

        if (array_key_exists('remoteId', $data)) {
            $contentTypeCreateStruct->remoteId = $data['remoteId'];
        }

        if (array_key_exists('urlAliasSchema', $data)) {
            $contentTypeCreateStruct->urlAliasSchema = $data['urlAliasSchema'];
        }

        // @todo XSD says that nameSchema is mandatory, but it is not in create struct
        if (array_key_exists('nameSchema', $data)) {
            $contentTypeCreateStruct->nameSchema = $data['nameSchema'];
        }

        // @todo XSD says that isContainer is mandatory, but it is not in create struct
        if (array_key_exists('isContainer', $data)) {
            $contentTypeCreateStruct->isContainer = $this->parserTools->parseBooleanValue($data['isContainer']);
        }

        // @todo XSD says that defaultSortField is mandatory, but it is not in create struct
        if (array_key_exists('defaultSortField', $data)) {
            $contentTypeCreateStruct->defaultSortField = $this->parserTools->parseDefaultSortField($data['defaultSortField']);
        }

        // @todo XSD says that defaultSortOrder is mandatory, but it is not in create struct
        if (array_key_exists('defaultSortOrder', $data)) {
            $contentTypeCreateStruct->defaultSortOrder = $this->parserTools->parseDefaultSortOrder($data['defaultSortOrder']);
        }

        // @todo XSD says that defaultAlwaysAvailable is mandatory, but it is not in create struct
        if (array_key_exists('defaultAlwaysAvailable', $data)) {
            $contentTypeCreateStruct->defaultAlwaysAvailable = $this->parserTools->parseBooleanValue($data['defaultAlwaysAvailable']);
        }

        if (array_key_exists('names', $data)) {
            if (!is_array($data['names'])
                || !array_key_exists('value', $data['names'])
                || !is_array($data['names']['value'])
            ) {
                throw new Exceptions\Parser("Invalid 'names' element for ContentTypeCreate.");
            }

            $contentTypeCreateStruct->names = $this->parserTools->parseTranslatableList($data['names']);
        }

        // @todo XSD says that descriptions is mandatory, but content type can be created without descriptions
        if (array_key_exists('descriptions', $data)) {
            if (!is_array($data['descriptions'])
                || !array_key_exists('value', $data['descriptions'])
                || !is_array($data['descriptions']['value'])
            ) {
                throw new Exceptions\Parser("Invalid 'descriptions' element for ContentTypeCreate.");
            }

            $contentTypeCreateStruct->descriptions = $this->parserTools->parseTranslatableList($data['descriptions']);
        }

        // @todo 1: XSD says that modificationDate is mandatory, but it is not in create struct
        // @todo 2: mismatch between XSD naming and create struct naming
        if (array_key_exists('modificationDate', $data)) {
            $contentTypeCreateStruct->creationDate = new DateTime($data['modificationDate']);
        }

        if (array_key_exists('User', $data)) {
            if (!array_key_exists('_href', $data['User'])) {
                throw new Exceptions\Parser("Missing '_href' attribute for the User element in ContentTypeCreate.");
            }

            $contentTypeCreateStruct->creatorId = (int)$this->uriParser->getAttributeFromUri($data['User']['_href'], 'userId');
        }

        if (!array_key_exists('FieldDefinitions', $data)) {
            throw new Exceptions\Parser("Missing 'FieldDefinitions' element for ContentTypeCreate.");
        }

        if (
            !is_array($data['FieldDefinitions'])
            || !array_key_exists('FieldDefinition', $data['FieldDefinitions'])
            || !is_array($data['FieldDefinitions']['FieldDefinition'])
        ) {
            throw new Exceptions\Parser("Invalid 'FieldDefinitions' element for ContentTypeCreate.");
        }

        // With no field definitions given and when ContentType is immediately published we must return HTTP 400 BadRequest,
        // instead of relying on service to throw InvalidArgumentException
        if (isset($data['__publish']) && $data['__publish'] === true && empty($data['FieldDefinitions']['FieldDefinition'])) {
            throw new Exceptions\Parser('ContentTypeCreate should provide at least one Field definition.');
        }

        foreach ($data['FieldDefinitions']['FieldDefinition'] as $fieldDefinitionData) {
            if (!is_array($fieldDefinitionData)) {
                throw new Exceptions\Parser("Invalid 'FieldDefinition' element for ContentTypeCreate.");
            }

            $contentTypeCreateStruct->addFieldDefinition(
                $this->fieldDefinitionCreateParser->parse($fieldDefinitionData, $parsingDispatcher)
            );
        }

        return $contentTypeCreateStruct;
    }
}
