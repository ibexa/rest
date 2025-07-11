<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\User\UserCreateStruct;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;
use Ibexa\Rest\Input\FieldTypeParser;
use Ibexa\Rest\Input\ParserTools;

/**
 * Parser for UserCreate.
 */
class UserCreate extends BaseParser
{
    protected UserService $userService;

    protected ContentTypeService $contentTypeService;

    protected FieldTypeParser $fieldTypeParser;

    protected ParserTools $parserTools;

    public function __construct(
        UserService $userService,
        ContentTypeService $contentTypeService,
        FieldTypeParser $fieldTypeParser,
        ParserTools $parserTools,
    ) {
        $this->userService = $userService;
        $this->contentTypeService = $contentTypeService;
        $this->fieldTypeParser = $fieldTypeParser;
        $this->parserTools = $parserTools;
    }

    /**
     * @param array{
     *     ContentType?: array{_href: string},
     *     mainLanguageCode: string,
     *     login: string,
     *     email: string,
     *     password: string,
     *     Section?: array{_href: string},
     *     remoteId?: string,
     *     enabled?: bool|string,
     *     fields: array{
     *         field: array<
     *             array{
     *                 fieldDefinitionIdentifier: string,
     *                 fieldValue: mixed,
     *                 languageCode?: string
     *             }
     *         >
     *     }
     * } $data
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): UserCreateStruct
    {
        $contentType = null;
        if (array_key_exists('ContentType', $data) && is_array($data['ContentType'])) {
            if (!array_key_exists('_href', $data['ContentType'])) {
                throw new Exceptions\Parser("Missing '_href' attribute for the ContentType element in UserCreate.");
            }

            $contentType = $this->contentTypeService->loadContentType(
                $this->uriParser->getAttributeFromUri($data['ContentType']['_href'], 'contentTypeId')
            );
        }

        if (!array_key_exists('mainLanguageCode', $data)) {
            throw new Exceptions\Parser("Missing 'mainLanguageCode' element for UserCreate.");
        }

        if (!array_key_exists('login', $data)) {
            throw new Exceptions\Parser("Missing 'login' element for UserCreate.");
        }

        if (!array_key_exists('email', $data)) {
            throw new Exceptions\Parser("Missing 'email' element for UserCreate.");
        }

        if (!array_key_exists('password', $data)) {
            throw new Exceptions\Parser("Missing 'password' element for UserCreate.");
        }

        $userCreateStruct = $this->userService->newUserCreateStruct(
            $data['login'],
            $data['email'],
            $data['password'],
            $data['mainLanguageCode'],
            $contentType
        );

        if (array_key_exists('Section', $data) && is_array($data['Section'])) {
            if (!array_key_exists('_href', $data['Section'])) {
                throw new Exceptions\Parser("Missing '_href' attribute for the Section element in UserCreate.");
            }

            $userCreateStruct->sectionId = (int)$this->uriParser->getAttributeFromUri($data['Section']['_href'], 'sectionId');
        }

        if (array_key_exists('remoteId', $data)) {
            $userCreateStruct->remoteId = $data['remoteId'];
        }

        if (array_key_exists('enabled', $data)) {
            $userCreateStruct->enabled = $this->parserTools->parseBooleanValue($data['enabled']);
        }

        if (!array_key_exists('fields', $data) || !is_array($data['fields']) || !is_array($data['fields']['field'])) {
            throw new Exceptions\Parser("Missing or invalid 'fields' element for UserCreate.");
        }

        foreach ($data['fields']['field'] as $fieldData) {
            if (!array_key_exists('fieldDefinitionIdentifier', $fieldData)) {
                throw new Exceptions\Parser("Missing 'fieldDefinitionIdentifier' element in field data for UserCreate.");
            }

            $fieldDefinition = $userCreateStruct->contentType->getFieldDefinition($fieldData['fieldDefinitionIdentifier']);
            if (!$fieldDefinition) {
                throw new Exceptions\Parser(
                    "'{$fieldData['fieldDefinitionIdentifier']}' is an invalid Field definition identifier for the '{$userCreateStruct->contentType->identifier}' content type in UserCreate."
                );
            }

            if (!array_key_exists('fieldValue', $fieldData)) {
                throw new Exceptions\Parser("Missing 'fieldValue' element for the '{$fieldData['fieldDefinitionIdentifier']}' identifier in UserCreate.");
            }

            $fieldValue = $this->fieldTypeParser->parseValue($fieldDefinition->fieldTypeIdentifier, $fieldData['fieldValue']);

            $languageCode = null;
            if (array_key_exists('languageCode', $fieldData)) {
                $languageCode = $fieldData['languageCode'];
            }

            $userCreateStruct->setField($fieldData['fieldDefinitionIdentifier'], $fieldValue, $languageCode);
        }

        return $userCreateStruct;
    }
}
