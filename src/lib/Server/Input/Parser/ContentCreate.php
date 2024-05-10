<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser;

use DateTime;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;
use Ibexa\Rest\Input\FieldTypeParser;
use Ibexa\Rest\Input\ParserTools;
use Ibexa\Rest\Server\Values\RestContentCreateStruct;

/**
 * Parser for ContentCreate.
 */
class ContentCreate extends BaseParser
{
    /**
     * Content service.
     *
     * @var \Ibexa\Contracts\Core\Repository\ContentService
     */
    protected $contentService;

    /**
     * ContentType service.
     *
     * @var \Ibexa\Contracts\Core\Repository\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * FieldType parser.
     *
     * @var \Ibexa\Rest\Input\FieldTypeParser
     */
    protected $fieldTypeParser;

    /**
     * LocationCreate parser.
     *
     * @var \Ibexa\Rest\Server\Input\Parser\LocationCreate
     */
    protected $locationCreateParser;

    /**
     * Parser tools.
     *
     * @var \Ibexa\Rest\Input\ParserTools
     */
    protected $parserTools;

    /**
     * Construct.
     *
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentService
     * @param \Ibexa\Contracts\Core\Repository\ContentTypeService $contentTypeService
     * @param \Ibexa\Rest\Input\FieldTypeParser $fieldTypeParser
     * @param \Ibexa\Rest\Server\Input\Parser\LocationCreate $locationCreateParser
     * @param \Ibexa\Rest\Input\ParserTools $parserTools
     */
    public function __construct(
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        FieldTypeParser $fieldTypeParser,
        LocationCreate $locationCreateParser,
        ParserTools $parserTools
    ) {
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->fieldTypeParser = $fieldTypeParser;
        $this->locationCreateParser = $locationCreateParser;
        $this->parserTools = $parserTools;
    }

    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \Ibexa\Contracts\Rest\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \Ibexa\Rest\Server\Values\RestContentCreateStruct
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!array_key_exists('LocationCreate', $data) || !is_array($data['LocationCreate'])) {
            throw new Exceptions\Parser("Missing or invalid 'LocationCreate' element for ContentCreate.");
        }

        $locationCreateStruct = $this->locationCreateParser->parse($data['LocationCreate'], $parsingDispatcher);

        if (!array_key_exists('ContentType', $data) || !is_array($data['ContentType'])) {
            throw new Exceptions\Parser("Missing or invalid 'ContentType' element for ContentCreate.");
        }

        if (!array_key_exists('_href', $data['ContentType'])) {
            throw new Exceptions\Parser("Missing '_href' attribute for the ContentType element in ContentCreate.");
        }

        if (!array_key_exists('mainLanguageCode', $data)) {
            throw new Exceptions\Parser("Missing 'mainLanguageCode' element for ContentCreate.");
        }

        $contentType = $this->contentTypeService->loadContentType(
            $this->requestParser->parseHref($data['ContentType']['_href'], 'contentTypeId')
        );

        $contentCreateStruct = $this->contentService->newContentCreateStruct($contentType, $data['mainLanguageCode']);

        if (array_key_exists('Section', $data) && is_array($data['Section'])) {
            if (!array_key_exists('_href', $data['Section'])) {
                throw new Exceptions\Parser("Missing '_href' attribute for the Section element in ContentCreate.");
            }

            $contentCreateStruct->sectionId = $this->requestParser->parseHref($data['Section']['_href'], 'sectionId');
        }

        if (array_key_exists('alwaysAvailable', $data)) {
            $contentCreateStruct->alwaysAvailable = $this->parserTools->parseBooleanValue($data['alwaysAvailable']);
        }

        if (array_key_exists('remoteId', $data)) {
            $contentCreateStruct->remoteId = $data['remoteId'];
        }

        if (array_key_exists('modificationDate', $data)) {
            $contentCreateStruct->modificationDate = new DateTime($data['modificationDate']);
        }

        if (array_key_exists('User', $data) && is_array($data['User'])) {
            if (!array_key_exists('_href', $data['User'])) {
                throw new Exceptions\Parser("Missing '_href' attribute for the User element in ContentCreate.");
            }

            $contentCreateStruct->ownerId = $this->requestParser->parseHref($data['User']['_href'], 'userId');
        }

        if (!array_key_exists('fields', $data) || !is_array($data['fields']) || !is_array($data['fields']['field'])) {
            throw new Exceptions\Parser("Missing or invalid 'fields' element for ContentCreate.");
        }

        foreach ($data['fields']['field'] as $fieldData) {
            if (!array_key_exists('fieldDefinitionIdentifier', $fieldData)) {
                throw new Exceptions\Parser("Missing 'fieldDefinitionIdentifier' element in Field data for ContentCreate.");
            }

            $fieldDefinition = $contentType->getFieldDefinition($fieldData['fieldDefinitionIdentifier']);
            if (!$fieldDefinition) {
                throw new Exceptions\Parser(
                    "'{$fieldData['fieldDefinitionIdentifier']}' is an invalid Field definition identifier for the '{$contentType->identifier}' content type in ContentCreate."
                );
            }

            if (!array_key_exists('fieldValue', $fieldData)) {
                throw new Exceptions\Parser("Missing 'fieldValue' element for the '{$fieldData['fieldDefinitionIdentifier']}' identifier in ContentCreate.");
            }

            $fieldValue = $this->fieldTypeParser->parseValue(
                $fieldDefinition->fieldTypeIdentifier,
                $fieldData['fieldValue']
            );

            $languageCode = null;
            if (array_key_exists('languageCode', $fieldData)) {
                $languageCode = $fieldData['languageCode'];
            }

            $contentCreateStruct->setField($fieldData['fieldDefinitionIdentifier'], $fieldValue, $languageCode);
        }

        return new RestContentCreateStruct($contentCreateStruct, $locationCreateStruct);
    }
}

class_alias(ContentCreate::class, 'EzSystems\EzPlatformRest\Server\Input\Parser\ContentCreate');
