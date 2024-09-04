<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\BadStateException;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentTypeFieldDefinitionValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentTypeValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType as APIContentType;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroupCreateStruct;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroupUpdateStruct;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\BadRequestException;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/content/types',
    name: 'List content types',
    openapi: new Model\Operation(
        summary: 'Returns a list of content types.',
        tags: [
            'Type',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the list of content type info objects or content types (including Field definitions) is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - returns a list of content types.',
                'content' => [
                    'application/vnd.ibexa.api.ContentTypeInfoList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeInfoList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/GET/ContentTypeInfoList.xml.example',
                    ],
                    'application/vnd.ibexa.api.ContentTypeInfoList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeInfoListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/GET/ContentTypeInfoList.json.example',
                    ],
                    'application/vnd.ibexa.api.ContentTypeList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeList',
                        ],
                    ],
                    'application/vnd.ibexa.api.ContentTypeList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeListWrapper',
                        ],
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user has no permission to read the content types.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/content/types/{contentTypeId}',
    name: 'Get content type',
    openapi: new Model\Operation(
        summary: 'Returns the content type with the provided ID.',
        tags: [
            'Type',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the content type is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'If-None-Match',
                in: 'header',
                required: true,
                description: 'ETag',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'contentTypeId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - returns the content type.',
                'content' => [
                    'application/vnd.ibexa.api.ContentType+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentType',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/draft/PUBLISH/ContentType.xml.example',
                    ],
                    'application/vnd.ibexa.api.ContentType+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/GET/ContentType.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to read this content type.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The content type does not exist.',
            ],
        ],
    ),
)]
#[Post(
    uriTemplate: '/content/types/{contentTypeId}',
    name: 'Create Draft',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Creates a draft and updates it with the given data.',
        tags: [
            'Type',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the new content type draft is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The content type Update schema encoded in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'contentTypeId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.ContentTypeUpdate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ContentTypeUpdate',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/POST/ContentTypeUpdate.xml.example',
                ],
                'application/vnd.ibexa.api.ContentTypeUpdate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ContentTypeUpdateWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/POST/ContentTypeUpdate.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_CREATED => [
                'description' => 'Draft created.',
                'content' => [
                    'application/vnd.ibexa.api.ContentTypeInfo+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeInfo',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/draft/PATCH/ContentTypeInfo.xml.example',
                    ],
                    'application/vnd.ibexa.api.ContentTypeInfo+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeInfoWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/draft/PATCH/ContentTypeInfo.json.example',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - The input does not match the input schema definition.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to create the draft.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - A content type with the given new identifier already exists. A draft already exists.',
            ],
        ],
    ),
)]
#[Delete(
    uriTemplate: '/content/types/{contentTypeId}',
    name: 'Delete content type',
    openapi: new Model\Operation(
        summary: 'Deletes the provided content type.',
        tags: [
            'Type',
        ],
        parameters: [
            new Model\Parameter(
                name: 'contentTypeId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'No Content - content type deleted.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to delete this content type.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - There are object instances of this content type.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The content type does not exist.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/content/types/{contentTypeId}/fieldDefinitions',
    name: 'Get Field definition list',
    openapi: new Model\Operation(
        summary: 'Returns all Field definitions of the provided content type.',
        tags: [
            'Type',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the Field definitions are returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'contentTypeId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - return a list of Field definitions.',
                'content' => [
                    'application/vnd.ibexa.api.FieldDefinitionList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/FieldDefinitions',
                        ],
                    ],
                    'application/vnd.ibexa.api.FieldDefinitionList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/FieldDefinitionsWrapper',
                        ],
                    ],
                ],
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The content type does not exist.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/content/types/{contentTypeId}/fieldDefinitions/{fieldDefinitionId}',
    name: 'Get Field definition',
    openapi: new Model\Operation(
        summary: 'Returns the Field definition by the given ID.',
        tags: [
            'Type',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the Field definition is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'contentTypeId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'fieldDefinitionId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - returns the Field definition.',
                'content' => [
                    'application/vnd.ibexa.api.FieldDefinition+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/FieldDefinition',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/field_definition_id/GET/FieldDefinition.xml.example',
                    ],
                    'application/vnd.ibexa.api.FieldDefinition+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/FieldDefinitionWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/field_definition_id/GET/FieldDefinition.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to read the content type.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The content type does not exist.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/content/types/{contentTypeId}/draft',
    name: 'Get content type draft',
    openapi: new Model\Operation(
        summary: 'Returns the draft of the content type with the provided ID.',
        tags: [
            'Type',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the content type is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'contentTypeId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - returns the content type.',
                'content' => [
                    'application/vnd.ibexa.api.ContentType+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentType',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/draft/PUBLISH/ContentType.xml.example',
                    ],
                    'application/vnd.ibexa.api.ContentType+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/GET/ContentType.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to read this content type.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The content type does not exist or does not have a draft.',
            ],
        ],
    ),
)]
#[Patch(
    uriTemplate: '/content/types/{contentTypeId}/draft',
    name: 'Update content type draft',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Updates metadata of a draft. This method does not handle Field definitions. PATCH or POST with header X-HTTP-Method-Override PATCH.',
        tags: [
            'Type',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the new content type draft is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The content type update schema encoded in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'contentTypeId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.ContentTypeUpdate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ContentTypeUpdate',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/POST/ContentTypeUpdate.xml.example',
                ],
                'application/vnd.ibexa.api.ContentTypeUpdate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ContentTypeUpdateWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/POST/ContentTypeUpdate.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_OK => [
                'description' => 'Draft metadata updated.',
                'content' => [
                    'application/vnd.ibexa.api.ContentTypeInfo+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeInfo',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/draft/PATCH/ContentTypeInfo.xml.example',
                    ],
                    'application/vnd.ibexa.api.ContentTypeInfo+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeInfoWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/draft/PATCH/ContentTypeInfo.json.example',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - The input does not match the input schema definition.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to update the draft.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - A content type with the given new identifier already exists.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - There is no draft for this content type.',
            ],
        ],
    ),
)]
#[Delete(
    uriTemplate: '/content/types/{contentTypeId}/draft',
    name: 'Delete content type draft',
    openapi: new Model\Operation(
        summary: 'Deletes the provided content type draft.',
        tags: [
            'Type',
        ],
        parameters: [
            new Model\Parameter(
                name: 'contentTypeId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'No Content - content type draft deleted.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to delete this content type draft.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The content type draft does not exist.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/content/types/{contentTypeId}/draft/fieldDefinitions',
    name: 'Get Draft Field definition list',
    openapi: new Model\Operation(
        summary: 'Returns all Field definitions of the provided content type Draft.',
        tags: [
            'Type',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the Field definitions are returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'contentTypeId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - return a list of Field definitions.',
                'content' => [
                    'application/vnd.ibexa.api.FieldDefinitionList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/FieldDefinitions',
                        ],
                    ],
                    'application/vnd.ibexa.api.FieldDefinitionList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/FieldDefinitionsWrapper',
                        ],
                    ],
                ],
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The content type draft does not exist.',
            ],
        ],
    ),
)]
#[Post(
    uriTemplate: '/content/types/{contentTypeId}/draft/fieldDefinitions',
    name: 'Add content type Draft Field definition',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Creates a new Field definition for the given content type.',
        tags: [
            'Type',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the new Field definition is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The Field Definition Create schema encoded in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'contentTypeId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.FieldDefinitionCreate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/FieldDefinitionCreate',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/draft/field_definitions/POST/FieldDefinitionCreate.xml.example',
                ],
                'application/vnd.ibexa.api.FieldDefinitionCreate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/FieldDefinitionCreateWrapper',
                    ],
                ],
            ]),
        ),
        responses: [
            Response::HTTP_CREATED => [
                'description' => 'Field definition created.',
                'content' => [
                    'application/vnd.ibexa.api.FieldDefinition+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/FieldDefinition',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/field_definition_id/GET/FieldDefinition.xml.example',
                    ],
                    'application/vnd.ibexa.api.FieldDefinition+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/FieldDefinitionWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/field_definition_id/GET/FieldDefinition.json.example',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - The input does not match the input schema definition or validation on the Field definition fails.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to add a Field definition.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - A Field definition with the same identifier already exists in the given content type. The Field definition is of singular type, already existing in the given content type. The Field definition you want to add is of a type that can\'t be added to a content type that already has content instances.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/content/types/{contentTypeId}/draft/fieldDefinitions/{fieldDefinitionId}',
    name: 'Get content type Draft Field definition',
    openapi: new Model\Operation(
        summary: 'Returns the Field definition by the given ID.',
        tags: [
            'Type',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the Field definition is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'contentTypeId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'fieldDefinitionId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - returns the Field definition.',
                'content' => [
                    'application/vnd.ibexa.api.FieldDefinition+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/FieldDefinition',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/field_definition_id/GET/FieldDefinition.xml.example',
                    ],
                    'application/vnd.ibexa.api.FieldDefinition+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/FieldDefinitionWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/field_definition_id/GET/FieldDefinition.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to read the content type draft.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The content type or draft does not exist.',
            ],
        ],
    ),
)]
#[Patch(
    uriTemplate: '/content/types/{contentTypeId}/draft/fieldDefinitions/{fieldDefinitionId}',
    name: 'Update content type Draft Field definition',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Updates the attributes of a Field definition.',
        tags: [
            'Type',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the updated Field definition is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The Field definition update schema encoded in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'contentTypeId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'fieldDefinitionId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.FieldDefinitionUpdate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/FieldDefinitionUpdate',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/draft/field_definitions/field_definition_id/PATCH/FieldDefinitionUpdate.xml.example',
                ],
                'application/vnd.ibexa.api.FieldDefinitionUpdate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/FieldDefinitionUpdateWrapper',
                    ],
                ],
            ]),
        ),
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - attributes updated.',
                'content' => [
                    'application/vnd.ibexa.api.FieldDefinition+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/FieldDefinition',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/field_definition_id/GET/FieldDefinition.xml.example',
                    ],
                    'application/vnd.ibexa.api.FieldDefinition+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/FieldDefinitionWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/field_definition_id/GET/FieldDefinition.json.example',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - The input does not match the input schema definition.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to update the Field definition.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - A Field definition with the given identifier already exists in the given content type.',
            ],
        ],
    ),
)]
#[Delete(
    uriTemplate: '/content/types/{contentTypeId}/draft/fieldDefinitions/{fieldDefinitionId}',
    name: 'Delete content type Draft Field definition',
    openapi: new Model\Operation(
        summary: 'Deletes the provided Field definition.',
        tags: [
            'Type',
        ],
        parameters: [
            new Model\Parameter(
                name: 'contentTypeId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'fieldDefinitionId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'No Content - Field definition deleted.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to delete this content type.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - There is no draft of the content type assigned to the authenticated user.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/content/types/{contentTypeId}/groups',
    name: 'Get groups of content type',
    openapi: new Model\Operation(
        summary: 'Returns the content type group to which content type belongs to.',
        tags: [
            'Type',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the content type group list is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'contentTypeId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'content' => [
                    'application/vnd.ibexa.api.ContentTypeGroupRefList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeGroupRefList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/groups/id/DELETE/ContentTypeGroupRefList.xml.example',
                    ],
                    'application/vnd.ibexa.api.ContentTypeGroupRefList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeGroupRefListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/groups/id/DELETE/ContentTypeGroupRefList.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to read this content type.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The content type does not exist.',
            ],
        ],
    ),
)]
#[Post(
    uriTemplate: '/content/types/{contentTypeId}/groups',
    name: 'Link group to content type',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapiContext: ['requestBody' => false],
    openapi: new Model\Operation(
        summary: 'Links a content type group to the content type and returns the updated group list.',
        tags: [
            'Type',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the updated content type group list is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'contentTypeId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'content' => [
                    'application/vnd.ibexa.api.ContentTypeGroupRefList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeGroupRefList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/groups/id/DELETE/ContentTypeGroupRefList.xml.example',
                    ],
                    'application/vnd.ibexa.api.ContentTypeGroupRefList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeGroupRefListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/groups/id/DELETE/ContentTypeGroupRefList.json.example',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - The input does not match the input schema definition.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to add a group.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - The content type is already assigned to the group.',
            ],
        ],
    ),
)]
#[Delete(
    uriTemplate: '/content/types/{contentTypeId}/groups/{id}',
    name: 'Unlink group from content type',
    openapi: new Model\Operation(
        summary: 'Removes the given group from the content type and returns the updated group list.',
        tags: [
            'Type',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the updated content type group list is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'contentTypeId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'content' => [
                    'application/vnd.ibexa.api.ContentTypeGroupRefList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeGroupRefList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/groups/id/DELETE/ContentTypeGroupRefList.xml.example',
                    ],
                    'application/vnd.ibexa.api.ContentTypeGroupRefList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeGroupRefListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/groups/id/DELETE/ContentTypeGroupRefList.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to delete this content type.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - content type cannot be unlinked from the only remaining group.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The resource does not exist.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/content/typegroups',
    name: 'Get content type groups',
    openapi: new Model\Operation(
        summary: 'Returns a list of all content type groups. If an identifier is provided, loads the content type group for this identifier.',
        tags: [
            'Type Groups',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the content type group list is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - returns a list of content type groups.',
                'content' => [
                    'application/vnd.ibexa.api.ContentTypeGroupList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeGroupList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/typegroups/GET/ContentTypeGroupList.xml.example',
                    ],
                    'application/vnd.ibexa.api.ContentTypeGroupList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeGroupListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/typegroups/GET/ContentTypeGroupList.json.example',
                    ],
                ],
            ],
            Response::HTTP_TEMPORARY_REDIRECT => [
                'description' => 'Temporary redirect.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user has no permission to read content types.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The content type group with the given identifier does not exist.',
            ],
        ],
    ),
)]
#[Post(
    uriTemplate: '/content/typegroups',
    name: 'Create content type group',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Creates a new content type group.',
        tags: [
            'Type Groups',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the new content type group is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The content type group input schema encoded in XML or JSON.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.ContentTypeGroupInput+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ContentTypeGroupInput',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/typegroups/POST/ContentTypeGroupInput.xml.example',
                ],
                'application/vnd.ibexa.api.ContentTypeGroupInput+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ContentTypeGroupInputWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/typegroups/POST/ContentTypeGroupInput.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_CREATED => [
                'description' => 'Content type group created.',
                'content' => [
                    'application/vnd.ibexa.api.ContentTypeGroup+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeGroup',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/typegroups/POST/ContentTypeGroup.xml.example',
                    ],
                    'application/vnd.ibexa.api.ContentTypeGroup+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeGroupWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/typegroups/content_type_group_id/PATCH/ContentTypeGroup.json.example',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - The input does not match the input schema definition.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to create this content type group.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - A content type group with the same identifier already exists.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/content/typegroups/{contentTypeGroupId}',
    name: 'Get content type group',
    openapi: new Model\Operation(
        summary: 'Returns the content type group with provided ID.',
        tags: [
            'Type Groups',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the content type group is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'If-None-Match',
                in: 'header',
                required: true,
                description: 'ETag',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'contentTypeGroupId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - returns the content type group.',
                'content' => [
                    'application/vnd.ibexa.api.ContentTypeGroup+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/GET/ContentTypeInfoList.xml.example',
                    ],
                    'application/vnd.ibexa.api.ContentTypeGroup+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/GET/ContentTypeInfoList.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to read this content type group.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The content type group does not exist.',
            ],
        ],
    ),
)]
#[Patch(
    uriTemplate: '/content/typegroups/{contentTypeGroupId}',
    name: 'Update content type group',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Updates a content type group. PATCH or POST with header X-HTTP-Method-Override PATCH.',
        tags: [
            'Type Groups',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the updated content type group is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The content type group input schema encoded in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'If-Match',
                in: 'header',
                required: true,
                description: 'ETag causes patching only if the specified ETag is the current one. Otherwise a 412 is returned.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'contentTypeGroupId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.ContentTypeGroupInput+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ContentTypeGroupInput',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/typegroups/POST/ContentTypeGroupInput.xml.example',
                ],
                'application/vnd.ibexa.api.ContentTypeGroupInput+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ContentTypeGroupInputWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/typegroups/POST/ContentTypeGroupInput.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_OK => [
                'description' => 'Content type group updated.',
                'content' => [
                    'application/vnd.ibexa.api.ContentTypeGroup+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeGroup',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/typegroups/POST/ContentTypeGroup.xml.example',
                    ],
                    'application/vnd.ibexa.api.ContentTypeGroup+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeGroupWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/typegroups/content_type_group_id/PATCH/ContentTypeGroup.json.example',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - The input does not match the input schema definition.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to create this content type group.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - A content type group with the given identifier already exists.',
            ],
            Response::HTTP_PRECONDITION_FAILED => [
                'description' => 'Error - The current ETag does not match the one provided in the If-Match header.',
            ],
        ],
    ),
)]
#[Delete(
    uriTemplate: '/content/typegroups/{contentTypeGroupId}',
    name: 'Delete content type group',
    openapi: new Model\Operation(
        summary: 'Deletes the provided content type group.',
        tags: [
            'Type Groups',
        ],
        parameters: [
            new Model\Parameter(
                name: 'contentTypeGroupId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'No Content - content type group deleted.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to delete this content type group.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - The content type group is not empty.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The content type group does not exist.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/content/typegroups/{contentTypeGroupId}/types',
    name: 'List content types for group',
    openapi: new Model\Operation(
        summary: 'Returns a list of content types in the provided group.',
        tags: [
            'Type Groups',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the list of content type info objects or content types (including Field definitions) is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'contentTypeGroupId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - returns a list on content types.',
                'content' => [
                    'application/vnd.ibexa.api.ContentTypeInfoList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeInfoList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/typegroups/content_type_group_id/types/GET/ContentTypeInfoList.xml.example',
                    ],
                    'application/vnd.ibexa.api.ContentTypeInfoList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeInfoListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/GET/ContentTypeInfoList.json.example',
                    ],
                    'application/vnd.ibexa.api.ContentTypeList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/GET/ContentTypeInfoList.xml.example',
                    ],
                    'application/vnd.ibexa.api.ContentTypeList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/GET/ContentTypeInfoList.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user has no permission to read the content types.',
            ],
        ],
    ),
)]
#[Post(
    uriTemplate: '/content/typegroups/{contentTypeGroupId}/types',
    name: 'Create content type',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Creates a new content type draft in the given content type group.',
        tags: [
            'Type Groups',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the new content type or draft is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The content type Create schema encoded in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'contentTypeGroupId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.ContentTypeCreate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ContentTypeCreate',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/typegroups/content_type_group_id/types/POST/ContentTypeCreate.xml.example',
                ],
                'application/vnd.ibexa.api.ContentTypeCreate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ContentTypeCreateWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/typegroups/content_type_group_id/types/POST/ContentTypeCreate.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_CREATED => [
                'description' => 'Content type created.',
                'content' => [
                    'application/vnd.ibexa.api.ContentType+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentType',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/draft/PUBLISH/ContentType.xml.example',
                    ],
                    'application/vnd.ibexa.api.ContentType+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeWrapper',
                        ],
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - The input does not match the input schema definition. Validation on a Field definition fails. Validation of the content type fails, e.g. multiple Fields of a same singular Field Type are provided. Publish is set to true and the input is not complete e.g. no Field definitions are provided.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to create this content type.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - A content type with same identifier already exists.',
            ],
        ],
    ),
)]
/**
 * ContentType controller.
 */
class ContentType extends RestController
{
    /**
     * Content type service.
     *
     * @var \Ibexa\Contracts\Core\Repository\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * Construct controller.
     *
     * @param \Ibexa\Contracts\Core\Repository\ContentTypeService $contentTypeService
     */
    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Creates a new content type group.
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     *
     * @return \Ibexa\Rest\Server\Values\CreatedContentTypeGroup
     */
    public function createContentTypeGroup(Request $request)
    {
        $createStruct = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        try {
            return new Values\CreatedContentTypeGroup(
                [
                    'contentTypeGroup' => $this->contentTypeService->createContentTypeGroup($createStruct),
                ]
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }
    }

    /**
     * Updates a content type group.
     *
     * @param $contentTypeGroupId
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup
     */
    public function updateContentTypeGroup($contentTypeGroupId, Request $request)
    {
        $createStruct = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        try {
            $this->contentTypeService->updateContentTypeGroup(
                $this->contentTypeService->loadContentTypeGroup($contentTypeGroupId),
                $this->mapToGroupUpdateStruct($createStruct)
            );

            return $this->contentTypeService->loadContentTypeGroup($contentTypeGroupId, Language::ALL);
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }
    }

    /**
     * Returns a list of content types of the group.
     *
     * @param string $contentTypeGroupId
     *
     * @return \Ibexa\Rest\Server\Values\ContentTypeList|\Ibexa\Rest\Server\Values\ContentTypeInfoList
     */
    public function listContentTypesForGroup($contentTypeGroupId, Request $request)
    {
        $contentTypes = $this->contentTypeService->loadContentTypes(
            $this->contentTypeService->loadContentTypeGroup($contentTypeGroupId, Language::ALL),
            Language::ALL
        );

        if ($this->getMediaType($request) === 'application/vnd.ibexa.api.contenttypelist') {
            return new Values\ContentTypeList($contentTypes, $request->getPathInfo());
        }

        return new Values\ContentTypeInfoList($contentTypes, $request->getPathInfo());
    }

    /**
     * The given content type group is deleted.
     *
     * @param mixed $contentTypeGroupId
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function deleteContentTypeGroup($contentTypeGroupId)
    {
        $contentTypeGroup = $this->contentTypeService->loadContentTypeGroup($contentTypeGroupId);

        $contentTypes = $this->contentTypeService->loadContentTypes($contentTypeGroup);
        if (!empty($contentTypes)) {
            throw new ForbiddenException('Only empty content type groups can be deleted');
        }

        $this->contentTypeService->deleteContentTypeGroup($contentTypeGroup);

        return new Values\NoContent();
    }

    /**
     * Returns a list of all content type groups.
     *
     * @return \Ibexa\Rest\Server\Values\ContentTypeGroupList
     */
    public function loadContentTypeGroupList(Request $request)
    {
        if ($request->query->has('identifier')) {
            $contentTypeGroup = $this->contentTypeService->loadContentTypeGroupByIdentifier(
                $request->query->get('identifier')
            );

            return new Values\TemporaryRedirect(
                $this->router->generate(
                    'ibexa.rest.load_content_type_group',
                    [
                        'contentTypeGroupId' => $contentTypeGroup->id,
                    ]
                )
            );
        }

        return new Values\ContentTypeGroupList(
            $this->contentTypeService->loadContentTypeGroups(Language::ALL)
        );
    }

    /**
     * Returns the content type group given by id.
     *
     * @param $contentTypeGroupId
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup
     */
    public function loadContentTypeGroup($contentTypeGroupId)
    {
        return $this->contentTypeService->loadContentTypeGroup($contentTypeGroupId, Language::ALL);
    }

    /**
     * Loads a content type.
     *
     * @param $contentTypeId
     *
     * @return \Ibexa\Rest\Server\Values\RestContentType
     */
    public function loadContentType($contentTypeId)
    {
        $contentType = $this->contentTypeService->loadContentType($contentTypeId, Language::ALL);

        return new Values\RestContentType(
            $contentType,
            $contentType->getFieldDefinitions()->toArray()
        );
    }

    /**
     * Returns a list of content types.
     *
     * @return \Ibexa\Rest\Server\Values\ContentTypeList|\Ibexa\Rest\Server\Values\ContentTypeInfoList
     */
    public function listContentTypes(Request $request)
    {
        if ($this->getMediaType($request) === 'application/vnd.ibexa.api.contenttypelist') {
            $return = new Values\ContentTypeList([], $request->getPathInfo());
        } else {
            $return = new Values\ContentTypeInfoList([], $request->getPathInfo());
        }

        if ($request->query->has('identifier')) {
            $return->contentTypes = [$this->loadContentTypeByIdentifier($request)];

            return $return;
        }

        if ($request->query->has('remoteId')) {
            $return->contentTypes = [
                $this->loadContentTypeByRemoteId($request),
            ];

            return $return;
        }

        $limit = null;
        if ($request->query->has('limit')) {
            $limit = (int)$request->query->get('limit', null);
            if ($limit <= 0) {
                throw new BadRequestException('wrong value for limit parameter');
            }
        }
        $contentTypes = $this->getContentTypeList();
        $sort = $request->query->get('sort');
        if ($request->query->has('orderby')) {
            $orderby = $request->query->get('orderby');
            $this->sortContentTypeList($contentTypes, $orderby, $sort);
        }
        $offset = $request->query->get('offset', 0);
        $return->contentTypes = array_slice($contentTypes, $offset, $limit);

        return $return;
    }

    /**
     * Loads a content type by its identifier.
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType
     */
    public function loadContentTypeByIdentifier(Request $request)
    {
        return $this->contentTypeService->loadContentTypeByIdentifier(
            $request->query->get('identifier'),
            Language::ALL
        );
    }

    /**
     * Loads a content type by its remote ID.
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType
     */
    public function loadContentTypeByRemoteId(Request $request)
    {
        return $this->contentTypeService->loadContentTypeByRemoteId(
            $request->query->get('remoteId'),
            Language::ALL
        );
    }

    /**
     * Creates a new content type draft in the given content type group.
     *
     * @param $contentTypeGroupId
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     * @throws \Ibexa\Rest\Server\Exceptions\BadRequestException
     *
     * @return \Ibexa\Rest\Server\Values\CreatedContentType
     */
    public function createContentType($contentTypeGroupId, Request $request)
    {
        $contentTypeGroup = $this->contentTypeService->loadContentTypeGroup($contentTypeGroupId);
        $publish = ($request->query->has('publish') && $request->query->get('publish') === 'true');

        try {
            $contentTypeDraft = $this->contentTypeService->createContentType(
                $this->inputDispatcher->parse(
                    new Message(
                        [
                            'Content-Type' => $request->headers->get('Content-Type'),
                            // @todo Needs refactoring! Temporary solution so parser has access to get parameters
                            '__publish' => $publish,
                        ],
                        $request->getContent()
                    )
                ),
                [$contentTypeGroup]
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        } catch (ContentTypeValidationException $e) {
            throw new BadRequestException($e->getMessage());
        } catch (ContentTypeFieldDefinitionValidationException $e) {
            throw new BadRequestException($e->getMessage());
        } catch (Exceptions\Parser $e) {
            throw new BadRequestException($e->getMessage());
        }

        if ($publish) {
            $this->contentTypeService->publishContentTypeDraft($contentTypeDraft);

            $contentType = $this->contentTypeService->loadContentType($contentTypeDraft->id, Language::ALL);

            return new Values\CreatedContentType(
                [
                    'contentType' => new Values\RestContentType(
                        $contentType,
                        $contentType->getFieldDefinitions()->toArray()
                    ),
                ]
            );
        }

        return new Values\CreatedContentType(
            [
                'contentType' => new Values\RestContentType(
                    $contentTypeDraft,
                    $contentTypeDraft->getFieldDefinitions()->toArray()
                ),
            ]
        );
    }

    /**
     * Copies a content type. The identifier of the copy is changed to
     * copy_of_<originalBaseIdentifier>_<newTypeId> and a new remoteId is generated.
     *
     * @param $contentTypeId
     *
     * @return \Ibexa\Rest\Server\Values\ResourceCreated
     */
    public function copyContentType($contentTypeId)
    {
        $copiedContentType = $this->contentTypeService->copyContentType(
            $this->contentTypeService->loadContentType($contentTypeId)
        );

        return new Values\ResourceCreated(
            $this->router->generate(
                'ibexa.rest.load_content_type',
                ['contentTypeId' => $copiedContentType->id]
            )
        );
    }

    /**
     * Creates a draft and updates it with the given data.
     *
     * @param $contentTypeId
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     *
     * @return \Ibexa\Rest\Server\Values\CreatedContentType
     */
    public function createContentTypeDraft($contentTypeId, Request $request)
    {
        $contentType = $this->contentTypeService->loadContentType($contentTypeId);

        try {
            $contentTypeDraft = $this->contentTypeService->createContentTypeDraft(
                $contentType
            );
        } catch (BadStateException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }

        $contentTypeUpdateStruct = $this->inputDispatcher->parse(
            new Message(
                [
                    'Content-Type' => $request->headers->get('Content-Type'),
                ],
                $request->getContent()
            )
        );

        try {
            $this->contentTypeService->updateContentTypeDraft(
                $contentTypeDraft,
                $contentTypeUpdateStruct
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }

        return new Values\CreatedContentType(
            [
                'contentType' => new Values\RestContentType(
                    // Reload the content type draft to get the updated values
                    $this->contentTypeService->loadContentTypeDraft(
                        $contentTypeDraft->id
                    )
                ),
            ]
        );
    }

    /**
     * Loads a content type draft.
     *
     * @param $contentTypeId
     *
     * @return \Ibexa\Rest\Server\Values\RestContentType
     */
    public function loadContentTypeDraft($contentTypeId)
    {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($contentTypeId);

        return new Values\RestContentType(
            $contentTypeDraft,
            $contentTypeDraft->getFieldDefinitions()->toArray()
        );
    }

    /**
     * Updates meta data of a draft. This method does not handle field definitions.
     *
     * @param $contentTypeId
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     *
     * @return \Ibexa\Rest\Server\Values\RestContentType
     */
    public function updateContentTypeDraft($contentTypeId, Request $request)
    {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($contentTypeId);
        $contentTypeUpdateStruct = $this->inputDispatcher->parse(
            new Message(
                [
                    'Content-Type' => $request->headers->get('Content-Type'),
                ],
                $request->getContent()
            )
        );

        try {
            $this->contentTypeService->updateContentTypeDraft(
                $contentTypeDraft,
                $contentTypeUpdateStruct
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }

        return new Values\RestContentType(
            // Reload the content type draft to get the updated values
            $this->contentTypeService->loadContentTypeDraft(
                $contentTypeDraft->id
            )
        );
    }

    /**
     * Creates a new field definition for the given content type draft.
     *
     * @param $contentTypeId
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     *
     * @return \Ibexa\Rest\Server\Values\CreatedFieldDefinition
     */
    public function addContentTypeDraftFieldDefinition($contentTypeId, Request $request)
    {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($contentTypeId);
        $fieldDefinitionCreate = $this->inputDispatcher->parse(
            new Message(
                [
                    'Content-Type' => $request->headers->get('Content-Type'),
                ],
                $request->getContent()
            )
        );

        try {
            $this->contentTypeService->addFieldDefinition(
                $contentTypeDraft,
                $fieldDefinitionCreate
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        } catch (ContentTypeFieldDefinitionValidationException $e) {
            throw new BadRequestException($e->getMessage());
        } catch (BadStateException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }

        $updatedDraft = $this->contentTypeService->loadContentTypeDraft($contentTypeId);
        foreach ($updatedDraft->getFieldDefinitions() as $fieldDefinition) {
            if ($fieldDefinition->identifier == $fieldDefinitionCreate->identifier) {
                return new Values\CreatedFieldDefinition(
                    [
                        'fieldDefinition' => new Values\RestFieldDefinition($updatedDraft, $fieldDefinition),
                    ]
                );
            }
        }

        throw new Exceptions\NotFoundException("Field definition not found: '{$request->getPathInfo()}'.");
    }

    /**
     * Loads field definitions for a given content type.
     *
     * @param $contentTypeId
     *
     * @return \Ibexa\Rest\Server\Values\FieldDefinitionList
     *
     * @todo Check why this isn't in the specs
     */
    public function loadContentTypeFieldDefinitionList($contentTypeId)
    {
        $contentType = $this->contentTypeService->loadContentType($contentTypeId, Language::ALL);

        return new Values\FieldDefinitionList(
            $contentType,
            $contentType->getFieldDefinitions()->toArray()
        );
    }

    /**
     * Returns the field definition given by id.
     *
     * @param $contentTypeId
     * @param $fieldDefinitionId
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     *
     * @return \Ibexa\Rest\Server\Values\RestFieldDefinition
     */
    public function loadContentTypeFieldDefinition($contentTypeId, $fieldDefinitionId, Request $request)
    {
        $contentType = $this->contentTypeService->loadContentType($contentTypeId, Language::ALL);

        foreach ($contentType->getFieldDefinitions() as $fieldDefinition) {
            if ($fieldDefinition->id == $fieldDefinitionId) {
                return new Values\RestFieldDefinition(
                    $contentType,
                    $fieldDefinition
                );
            }
        }

        throw new Exceptions\NotFoundException("Field definition not found: '{$request->getPathInfo()}'.");
    }

    /**
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function loadContentTypeFieldDefinitionByIdentifier(
        int $contentTypeId,
        string $fieldDefinitionIdentifier,
        Request $request
    ): Values\RestFieldDefinition {
        $contentType = $this->contentTypeService->loadContentType($contentTypeId);
        $fieldDefinition = $contentType->getFieldDefinition($fieldDefinitionIdentifier);
        $path = $this->router->generate(
            'ibexa.rest.load_content_type_field_definition_by_identifier',
            [
                'contentTypeId' => $contentType->id,
                'fieldDefinitionIdentifier' => $fieldDefinitionIdentifier,
            ]
        );

        if ($fieldDefinition === null) {
            throw new Exceptions\NotFoundException(
                sprintf("Field definition not found: '%s'.", $request->getPathInfo())
            );
        }

        return new Values\RestFieldDefinition(
            $contentType,
            $fieldDefinition,
            $path
        );
    }

    /**
     * Loads field definitions for a given content type draft.
     *
     * @param $contentTypeId
     *
     * @return \Ibexa\Rest\Server\Values\FieldDefinitionList
     */
    public function loadContentTypeDraftFieldDefinitionList($contentTypeId)
    {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($contentTypeId);

        return new Values\FieldDefinitionList(
            $contentTypeDraft,
            $contentTypeDraft->getFieldDefinitions()
        );
    }

    /**
     * Returns the draft field definition given by id.
     *
     * @param $contentTypeId
     * @param $fieldDefinitionId
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     *
     * @return \Ibexa\Rest\Server\Values\RestFieldDefinition
     */
    public function loadContentTypeDraftFieldDefinition($contentTypeId, $fieldDefinitionId, Request $request)
    {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($contentTypeId);

        foreach ($contentTypeDraft->getFieldDefinitions() as $fieldDefinition) {
            if ($fieldDefinition->id == $fieldDefinitionId) {
                return new Values\RestFieldDefinition(
                    $contentTypeDraft,
                    $fieldDefinition
                );
            }
        }

        throw new Exceptions\NotFoundException("Field definition not found: '{$request->getPathInfo()}'.");
    }

    /**
     * Updates the attributes of a field definition.
     *
     * @param $contentTypeId
     * @param $fieldDefinitionId
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     *
     * @return \Ibexa\Rest\Server\Values\FieldDefinitionList
     */
    public function updateContentTypeDraftFieldDefinition($contentTypeId, $fieldDefinitionId, Request $request)
    {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($contentTypeId);
        $fieldDefinitionUpdate = $this->inputDispatcher->parse(
            new Message(
                [
                    'Content-Type' => $request->headers->get('Content-Type'),
                    // @todo Needs refactoring! Temporary solution so parser has access to URL
                    'Url' => $request->getPathInfo(),
                ],
                $request->getContent()
            )
        );

        $fieldDefinition = null;
        foreach ($contentTypeDraft->getFieldDefinitions() as $fieldDef) {
            if ($fieldDef->id == $fieldDefinitionId) {
                $fieldDefinition = $fieldDef;
            }
        }

        if ($fieldDefinition === null) {
            throw new Exceptions\NotFoundException("Field definition not found: '{$request->getPathInfo()}'.");
        }

        try {
            $this->contentTypeService->updateFieldDefinition(
                $contentTypeDraft,
                $fieldDefinition,
                $fieldDefinitionUpdate
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }

        $updatedDraft = $this->contentTypeService->loadContentTypeDraft($contentTypeId);
        foreach ($updatedDraft->getFieldDefinitions() as $fieldDef) {
            if ($fieldDef->id == $fieldDefinitionId) {
                return new Values\RestFieldDefinition($updatedDraft, $fieldDef);
            }
        }

        throw new Exceptions\NotFoundException("Field definition not found: '{$request->getPathInfo()}'.");
    }

    /**
     * Deletes a field definition from a content type draft.
     *
     * @param $contentTypeId
     * @param $fieldDefinitionId
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function removeContentTypeDraftFieldDefinition($contentTypeId, $fieldDefinitionId, Request $request)
    {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($contentTypeId);

        $fieldDefinition = null;
        foreach ($contentTypeDraft->getFieldDefinitions() as $fieldDef) {
            if ($fieldDef->id == $fieldDefinitionId) {
                $fieldDefinition = $fieldDef;
            }
        }

        if ($fieldDefinition === null) {
            throw new Exceptions\NotFoundException("Field definition not found: '{$request->getPathInfo()}'.");
        }

        $this->contentTypeService->removeFieldDefinition(
            $contentTypeDraft,
            $fieldDefinition
        );

        return new Values\NoContent();
    }

    /**
     * Publishes a content type draft.
     *
     * @param $contentTypeId
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     *
     * @return \Ibexa\Rest\Server\Values\RestContentType
     */
    public function publishContentTypeDraft($contentTypeId)
    {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($contentTypeId);

        $fieldDefinitions = $contentTypeDraft->getFieldDefinitions();
        if (empty($fieldDefinitions)) {
            throw new ForbiddenException('Cannot publish an empty content type draft');
        }

        $this->contentTypeService->publishContentTypeDraft($contentTypeDraft);

        $publishedContentType = $this->contentTypeService->loadContentType($contentTypeDraft->id, Language::ALL);

        return new Values\RestContentType(
            $publishedContentType,
            $publishedContentType->getFieldDefinitions()->toArray()
        );
    }

    /**
     * The given content type is deleted.
     *
     * @param $contentTypeId
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function deleteContentType($contentTypeId)
    {
        $contentType = $this->contentTypeService->loadContentType($contentTypeId);

        try {
            $this->contentTypeService->deleteContentType($contentType);
        } catch (BadStateException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }

        return new Values\NoContent();
    }

    /**
     * The given content type draft is deleted.
     *
     * @param $contentTypeId
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function deleteContentTypeDraft($contentTypeId)
    {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($contentTypeId);
        $this->contentTypeService->deleteContentType($contentTypeDraft);

        return new Values\NoContent();
    }

    /**
     * Returns the content type groups the content type belongs to.
     *
     * @param $contentTypeId
     *
     * @return \Ibexa\Rest\Server\Values\ContentTypeGroupRefList
     */
    public function loadGroupsOfContentType($contentTypeId)
    {
        $contentType = $this->contentTypeService->loadContentType($contentTypeId, Language::ALL);

        return new Values\ContentTypeGroupRefList(
            $contentType,
            $contentType->getContentTypeGroups()
        );
    }

    /**
     * Links a content type group to the content type and returns the updated group list.
     *
     * @param mixed $contentTypeId
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     * @throws \Ibexa\Rest\Server\Exceptions\BadRequestException
     *
     * @return \Ibexa\Rest\Server\Values\ContentTypeGroupRefList
     */
    public function linkContentTypeToGroup($contentTypeId, Request $request)
    {
        $contentType = $this->contentTypeService->loadContentType($contentTypeId);

        try {
            $contentTypeGroupId = $this->requestParser->parseHref(
                $request->query->get('group'),
                'contentTypeGroupId'
            );
        } catch (Exceptions\InvalidArgumentException $e) {
            // Group URI does not match the required value
            throw new BadRequestException($e->getMessage());
        }

        $contentTypeGroup = $this->contentTypeService->loadContentTypeGroup($contentTypeGroupId);

        $existingContentTypeGroups = $contentType->getContentTypeGroups();
        $contentTypeInGroup = false;
        foreach ($existingContentTypeGroups as $existingGroup) {
            if ($existingGroup->id == $contentTypeGroup->id) {
                $contentTypeInGroup = true;
                break;
            }
        }

        if ($contentTypeInGroup) {
            throw new ForbiddenException('The content type is already linked to the provided group');
        }

        $this->contentTypeService->assignContentTypeGroup(
            $contentType,
            $contentTypeGroup
        );

        $existingContentTypeGroups[] = $contentTypeGroup;

        return new Values\ContentTypeGroupRefList(
            $contentType,
            $existingContentTypeGroups
        );
    }

    /**
     * Removes the given group from the content type and returns the updated group list.
     *
     * @param $contentTypeId
     * @param $contentTypeGroupId
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     *
     * @return \Ibexa\Rest\Server\Values\ContentTypeGroupRefList
     */
    public function unlinkContentTypeFromGroup($contentTypeId, $contentTypeGroupId)
    {
        $contentType = $this->contentTypeService->loadContentType($contentTypeId);
        $contentTypeGroup = $this->contentTypeService->loadContentTypeGroup($contentTypeGroupId);

        $existingContentTypeGroups = $contentType->getContentTypeGroups();
        $contentTypeInGroup = false;
        foreach ($existingContentTypeGroups as $existingGroup) {
            if ($existingGroup->id == $contentTypeGroup->id) {
                $contentTypeInGroup = true;
                break;
            }
        }

        if (!$contentTypeInGroup) {
            throw new Exceptions\NotFoundException('The content type is not in the provided group');
        }

        if (count($existingContentTypeGroups) == 1) {
            throw new ForbiddenException('Cannot unlink the content type from its only remaining group');
        }

        $this->contentTypeService->unassignContentTypeGroup(
            $contentType,
            $contentTypeGroup
        );

        $contentType = $this->contentTypeService->loadContentType($contentTypeId);

        return new Values\ContentTypeGroupRefList(
            $contentType,
            $contentType->getContentTypeGroups()
        );
    }

    /**
     * Converts the provided ContentTypeGroupCreateStruct to ContentTypeGroupUpdateStruct.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroupCreateStruct $createStruct
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroupUpdateStruct
     */
    private function mapToGroupUpdateStruct(ContentTypeGroupCreateStruct $createStruct)
    {
        return new ContentTypeGroupUpdateStruct(
            [
                'identifier' => $createStruct->identifier,
                'modifierId' => $createStruct->creatorId,
                'modificationDate' => $createStruct->creationDate,
            ]
        );
    }

    /**
     * @param array &$contentTypes
     * @param string $orderby
     *
     * @return mixed
     *
     * @throws \Ibexa\Rest\Server\Exceptions\BadRequestException
     */
    protected function sortContentTypeList(array &$contentTypes, $orderby, $sort = 'asc')
    {
        switch ($orderby) {
            case 'name':
                if ($sort === 'asc' || $sort === null) {
                    usort(
                        $contentTypes,
                        static function (APIContentType $contentType1, APIContentType $contentType2) {
                            return strcasecmp($contentType1->identifier, $contentType2->identifier);
                        }
                    );
                } elseif ($sort === 'desc') {
                    usort(
                        $contentTypes,
                        static function (APIContentType $contentType1, APIContentType $contentType2) {
                            return strcasecmp($contentType1->identifier, $contentType2->identifier) * -1;
                        }
                    );
                } else {
                    throw new BadRequestException('wrong value for sort parameter');
                }
                break;
            case 'lastmodified':
                if ($sort === 'asc' || $sort === null) {
                    usort(
                        $contentTypes,
                        static function ($timeObj3, $timeObj4) {
                            $timeObj3 = strtotime($timeObj3->modificationDate->format('Y-m-d H:i:s'));
                            $timeObj4 = strtotime($timeObj4->modificationDate->format('Y-m-d H:i:s'));

                            return $timeObj3 > $timeObj4;
                        }
                    );
                } elseif ($sort === 'desc') {
                    usort(
                        $contentTypes,
                        static function ($timeObj3, $timeObj4) {
                            $timeObj3 = strtotime($timeObj3->modificationDate->format('Y-m-d H:i:s'));
                            $timeObj4 = strtotime($timeObj4->modificationDate->format('Y-m-d H:i:s'));

                            return $timeObj3 < $timeObj4;
                        }
                    );
                } else {
                    throw new BadRequestException('wrong value for sort parameter');
                }
                break;
            default:
                throw new BadRequestException('wrong value for orderby parameter');
                break;
        }
    }

    /**
     * @return ContentType[]
     */
    protected function getContentTypeList()
    {
        $contentTypes = [];
        foreach ($this->contentTypeService->loadContentTypeGroups() as $contentTypeGroup) {
            $contentTypes = array_merge(
                $contentTypes,
                $this->contentTypeService->loadContentTypes($contentTypeGroup, Language::ALL)
            );
        }

        return $contentTypes;
    }
}
