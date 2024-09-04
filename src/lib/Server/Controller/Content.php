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
use Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Relation;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\BadRequestException;
use Ibexa\Rest\Server\Exceptions\ContentFieldValidationException as RESTContentFieldValidationException;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values;
use Ibexa\Rest\Server\Values\RestContentCreateStruct;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

#[Post(
    uriTemplate: '/content/views',
    name: 'Create View (deprecated)',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapiContext: ['requestBody' => false],
    openapi: new Model\Operation(
        summary: 'Executes a query and returns View including the results. The View input reflects the criteria model of the public PHP API. Deprecated as of eZ Platform 1.0 and will respond 301, use POST /views instead.',
        tags: [
            'Views',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'The View in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The View input in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_MOVED_PERMANENTLY => [
                'description' => 'Moved permanently.',
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - the input does not match the input schema definition.',
            ],
        ],
    ),
)]
#[Post(
    uriTemplate: '/content/objects',
    name: 'Create content item',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Creates a draft assigned to the authenticated user. If a different user ID is given in the input, the draft is assigned to the given user but this action requires special permissions for the authenticated user (this is useful for content staging where the transfer process does not have to authenticate with the user who created the content item in the source server). The user needs to publish the content item if it should be visible.',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'Content - If set, all information for the content item including the embedded current version is returned in XML or JSON format. ContentInfo - If set, all information for the content item (excluding the current version) is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The ContentCreate schema encoded in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.ContentCreate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ContentCreate',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/POST/ContentCreate.xml.example',
                ],
                'application/vnd.ibexa.api.ContentCreate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ContentCreateWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/POST/ContentCreate.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_CREATED => [
                'content' => [
                    'application/vnd.ibexa.api.Content+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Content',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/GET/Content.xml.example',
                    ],
                    'application/vnd.ibexa.api.Content+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/GET/Content.json.example',
                    ],
                    'application/vnd.ibexa.api.ContentInfo+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentInfoWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/PATCH/ContentInfo.xml.example',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - the input does not match the input schema definition or the validation on a field fails.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to create this Object in this Location.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the parent Location specified in the request body does not exist.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/content/objects',
    name: 'Load content by remote ID',
    openapi: new Model\Operation(
        summary: 'Loads content item for a given remote ID.',
        tags: [
            'Objects',
        ],
        parameters: [
        ],
        responses: [
            Response::HTTP_TEMPORARY_REDIRECT => [
                'description' => 'Temporary redirect.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the content with the given remote ID does not exist.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/content/objects/{contentId}',
    name: 'Load content',
    openapi: new Model\Operation(
        summary: 'Loads the content item for the given ID. Depending on the Accept header the current version is embedded (i.e. the current published version or if it does not exist, the draft of the authenticated user).',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'Content -	If set, all information for the content item including the embedded current version is returned in XML or JSON format. ContentInfo - If set, all information for the content item (excluding the current version) is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'If-None-Match',
                in: 'header',
                required: true,
                description: 'If the provided ETag matches the current ETag then a "304 Not Modified" is returned. The ETag changes if the meta data has changed, this happens also if there is a new published version.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'contentId',
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
                    'application/vnd.ibexa.api.Content+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Content',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/GET/Content.xml.example',
                    ],
                    'application/vnd.ibexa.api.Content+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/GET/Content.json.example',
                    ],
                    'application/vnd.ibexa.api.ContentInfo+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentInfo',
                        ],
                    ],
                    'application/vnd.ibexa.api.ContentInfo+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentInfoWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/PATCH/ContentInfo.xml.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to read this object. This could also happen if there is no published version yet and another user owns a draft of this content item.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the ID is not found.',
            ],
        ],
    ),
)]
#[Patch(
    uriTemplate: '/content/objects/{contentId}',
    name: 'Update content',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'This method updates the content metadata which is independent from a version. PATCH or POST with header X-HTTP-Method-Override PATCH.',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, all information for the content item (excluding the current version) is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'If-match',
                in: 'header',
                required: true,
                description: 'Causes to patch only if the specified ETag is the current one. Otherwise a 412 is returned.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The ContentUpdate schema encoded in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'contentId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.ContentUpdate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ContentInfo',
                    ],
                ],
                'application/vnd.ibexa.api.ContentUpdate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ContentInfoWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/PATCH/ContentInfo.xml.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_OK => [
                'content' => [
                    'application/vnd.ibexa.api.ContentInfo+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentInfo',
                        ],
                    ],
                    'application/vnd.ibexa.api.ContentInfo+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentInfoWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/PATCH/ContentInfo.xml.example',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - the input does not match the input schema definition.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to update this object.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the content ID does not exist.',
            ],
            Response::HTTP_PRECONDITION_FAILED => [
                'description' => 'Error - the current ETag does not match with the one provided in the If-Match header.',
            ],
            Response::HTTP_UNSUPPORTED_MEDIA_TYPE => [
                'description' => 'Error - the media-type is not one of those specified in headers.',
            ],
        ],
    ),
)]
#[Delete(
    uriTemplate: '/content/objects/{contentId}',
    name: 'Delete Content',
    openapi: new Model\Operation(
        summary: 'Deletes content item. If content item has multiple Locations, all of them will be deleted via delete a subtree.',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'contentId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'The content item is deleted.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - content item was not found.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to delete this content item.',
            ],
        ],
    ),
)]
#[Delete(
    uriTemplate: '/content/objects/{contentId}/translations/{languageCode}',
    name: 'Delete translation (permanently)',
    openapi: new Model\Operation(
        summary: 'Permanently deletes a translation from all versions of a content item.',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'contentId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'languageCode',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'No Content',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to delete content item (content/remove policy).',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the content item was not found.',
            ],
            Response::HTTP_NOT_ACCEPTABLE => [
                'description' => 'Error - the given translation does not exist for the content item.',
            ],
            Response::HTTP_CONFLICT => [
                'description' => 'Error - the specified translation is the only one any version has or is the main translation.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/content/objects/{contentId}/currentversion',
    name: 'Get current version',
    openapi: new Model\Operation(
        summary: 'Redirects to the current version of the content item.',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'contentId',
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
                    'application/vnd.ibexa.api.Version+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Version',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/versions/version_no/GET/Version.xml.example',
                    ],
                    'application/vnd.ibexa.api.Version+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/VersionWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/versions/version_no/GET/Version.json.example',
                    ],
                ],
            ],
            Response::HTTP_TEMPORARY_REDIRECT => [
                'description' => 'Temporary redirect.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the resource does not exist.',
            ],
        ],
    ),
)]
#[Post(
    uriTemplate: '/content/objects/{contentId}/currentversion',
    name: 'Create a draft from current version',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapiContext: ['requestBody' => false],
    openapi: new Model\Operation(
        summary: 'The system creates a new draft as a copy of the current version. COPY or POST with header X-HTTP-Method-Override COPY.',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the updated version is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'contentId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_CREATED => [
                'description' => 'Created',
                'content' => [
                    'application/vnd.ibexa.api.Version+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Version',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/versions/version_no/GET/Version.xml.example',
                    ],
                    'application/vnd.ibexa.api.Version+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/VersionWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/versions/version_no/GET/Version.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to update this content item.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - the current version is already a draft.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the content item was not found.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/content/objects/{contentId}/versions',
    name: 'List versions',
    openapi: new Model\Operation(
        summary: 'Returns a list of all versions of the content item. This method does not include fields and relations in the version elements of the response.',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the version list is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'contentId',
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
                    'application/vnd.ibexa.api.VersionList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/VersionList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/versions/GET/VersionList.xml.example',
                    ],
                    'application/vnd.ibexa.api.VersionList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/VersionListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/versions/GET/VersionList.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user has no permission to read the versions.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/content/objects/{contentId}/versions/{versionNo}',
    name: 'Load version',
    openapi: new Model\Operation(
        summary: 'Loads a specific version of a content item. This method returns Fields and relations.',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'If-None-Match',
                in: 'header',
                required: true,
                description: 'Only return the version if the given ETag is the not current one, otherwise a 304 is returned.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the version list is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'contentId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'versionNo',
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
                    'application/vnd.ibexa.api.Version+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Version',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/versions/version_no/GET/Version.xml.example',
                    ],
                    'application/vnd.ibexa.api.Version+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/VersionWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/versions/version_no/GET/Version.json.example',
                    ],
                ],
            ],
            Response::HTTP_NOT_MODIFIED => [
                'description' => 'Error - the ETag does not match the current one.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to read this content item.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the ID or version is not found.',
            ],
        ],
    ),
)]
#[Patch(
    uriTemplate: '/content/objects/{contentId}/versions/{versionNo}',
    name: 'Update version',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'A specific draft is updated. PATCH or POST with header X-HTTP-Method-Override PATCH.',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the updated version is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'If-match',
                in: 'header',
                required: true,
                description: 'Performs the patch only if the specified ETag is the current one.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The VersionUpdate schema encoded in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'contentId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'versionNo',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.VersionUpdate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/VersionUpdate',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/versions/version_no/PATCH/VersionUpdate.xml.example',
                ],
                'application/vnd.ibexa.api.VersionUpdate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/VersionUpdateWrapper',
                    ],
                ],
            ]),
        ),
        responses: [
            Response::HTTP_OK => [
                'content' => [
                    'application/vnd.ibexa.api.Version+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Version',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/versions/version_no/GET/Version.xml.example',
                    ],
                    'application/vnd.ibexa.api.Version+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/VersionWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/versions/version_no/GET/Version.json.example',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - the input does not match the input schema definition.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to update this version.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - the version is not allowed to change - i.e. version is not a DRAFT.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the content ID or version ID does not exist.',
            ],
            Response::HTTP_PRECONDITION_FAILED => [
                'description' => 'Error - the current ETag does not match with the one provided in the If-Match header.',
            ],
        ],
    ),
)]
#[Post(
    uriTemplate: '/content/objects/{contentId}/versions/{versionNo}',
    name: 'Create a draft from a version',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapiContext: ['requestBody' => false],
    openapi: new Model\Operation(
        summary: 'The system creates a new draft as a copy of the given version. COPY or POST with header X-HTTP-Method-Override COPY.',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the updated version is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'contentId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'versionNo',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_CREATED => [
                'description' => 'Created.',
                'content' => [
                    'application/vnd.ibexa.api.Version+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Version',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/versions/version_no/GET/Version.xml.example',
                    ],
                    'application/vnd.ibexa.api.Version+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/VersionWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/versions/version_no/GET/Version.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to update this content item.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the content item was not found.',
            ],
        ],
    ),
)]
#[Delete(
    uriTemplate: '/content/objects/{contentId}/versions/{versionNo}',
    name: 'Delete content version',
    openapi: new Model\Operation(
        summary: 'Deletes the content version.',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'contentId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'versionNo',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'No Content - the version is deleted.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the content item or version were not found.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to delete this version.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - the version is in published state.',
            ],
        ],
    ),
)]
#[Delete(
    uriTemplate: '/content/objects/{contentId}/versions/{versionNo}/translations/{languageCode}',
    name: 'Delete translation from version draft',
    openapi: new Model\Operation(
        summary: 'Removes a translation from a version draft.',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'contentId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'versionNo',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'languageCode',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'No Content - removes a translation from a version draft.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to delete this translation.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - the version is not in draft state.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the content item or version number were not found.',
            ],
            Response::HTTP_NOT_ACCEPTABLE => [
                'description' => 'Error - the given translation does not exist for the version.',
            ],
            Response::HTTP_CONFLICT => [
                'description' => 'Error - the specified translation is the only one the version has or is the main translation.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/content/objects/{contentId}/versions/{versionNo}/relations',
    name: 'Load Relations of content item version',
    openapi: new Model\Operation(
        summary: 'Loads the Relations of the given version.',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the Relation is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'contentId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'versionNo',
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
                    'application/vnd.ibexa.api.RelationList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RelationList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/versions/version_no/relations/GET/RelationList.xml.example',
                    ],
                    'application/vnd.ibexa.api.RelationList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RelationListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/versions/version_no/relations/GET/RelationList.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to read this content item.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the content item was not found.',
            ],
        ],
    ),
)]
#[Post(
    uriTemplate: '/content/objects/{contentId}/versions/{versionNo}/relations',
    name: 'Create new Relation',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Creates a new Relation of type COMMON for the given draft.',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the updated version is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The RelationCreate schema encoded in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'contentId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'versionNo',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.RelationCreate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/RelationCreate',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/versions/version_no/relations/POST/RelationCreate.xml.example',
                ],
                'application/vnd.ibexa.api.RelationCreate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/RelationCreateWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/versions/version_no/relations/POST/RelationCreate.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_CREATED => [
                'content' => [
                    'application/vnd.ibexa.api.Relation+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Relation',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/versions/version_no/relations/relation_id/GET/Relation.xml.example',
                    ],
                    'application/vnd.ibexa.api.Relation+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RelationWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/versions/version_no/relations/POST/Relation.json.example',
                    ],
                ],
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/content/objects/{contentId}/versions/{versionNo}/relations/{relationId}',
    name: 'Load Relation',
    openapi: new Model\Operation(
        summary: 'Loads a Relation for the given content item.',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the Relation is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'contentId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'versionNo',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'relationId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - loads a Relation for the given content item.',
                'content' => [
                    'application/vnd.ibexa.api.Relation+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Relation',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/versions/version_no/relations/relation_id/GET/Relation.xml.example',
                    ],
                    'application/vnd.ibexa.api.Relation+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RelationWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/versions/version_no/relations/POST/Relation.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to read this content item.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the content item with the given ID or the Relation does not exist.',
            ],
        ],
    ),
)]
#[Delete(
    uriTemplate: '/content/objects/{contentId}/versions/{versionNo}/relations/{relationId}',
    name: 'Delete Relation',
    openapi: new Model\Operation(
        summary: 'Deletes a Relation of the given draft.',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'contentId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'versionNo',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'relationId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'No Content - deleted a Relation of the given draft.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to delete this Relation.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - the Relation is not of type COMMON or the given version is not a draft.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - content item  or the Relation were not found in the given version.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/content/objects/{contentId}/relations',
    name: 'Load Relations of content item',
    openapi: new Model\Operation(
        summary: 'Redirects to the Relations of the current version.',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'contentId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_TEMPORARY_REDIRECT => [
                'description' => 'Temporary redirect.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to read this content item.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the content item was not found.',
            ],
        ],
    ),
)]
#[Post(
    uriTemplate: '/content/objects/{contentId}/locations',
    name: 'Create new Location for content item',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Creates a new Location for the given content item.',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the new Location is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The LocationCreate schema encoded in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'contentId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.LocationCreate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/LocationCreate',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/locations/POST/LocationCreate.xml.example',
                ],
                'application/vnd.ibexa.api.LocationCreate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/LocationCreateWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/locations/POST/LocationCreate.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_CREATED => [
                'content' => [
                    'application/vnd.ibexa.api.Location+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Location',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/locations/POST/Location.xml.example',
                    ],
                    'application/vnd.ibexa.api.Location+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/LocationWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/locations/POST/Location.json.example',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - the input does not match the input schema definition.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to create this Location.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - a Location under the given parent ID already exists.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/content/objects/{contentId}/locations',
    name: 'Get Locations for content item',
    openapi: new Model\Operation(
        summary: 'Loads all Locations for the given content item.',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the Location list is returned in XML or JSON format.',
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
                name: 'contentId',
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
                    'application/vnd.ibexa.api.LocationList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/LocationList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/locations/GET/LocationList.xml.example',
                    ],
                    'application/vnd.ibexa.api.LocationList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/LocationListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/locations/GET/LocationList.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to read this content item.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the content item with the given ID does not exist.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/content/objects/{contentId}/objectstates',
    name: 'Get Object states of content item',
    openapi: new Model\Operation(
        summary: 'Returns the Object states of a content item',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the Object states are returned in XML or JSON format.',
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
                name: 'contentId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - returns the Object state.',
                'content' => [
                    'application/vnd.ibexa.api.ContentObjectStates+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentObjectStates',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/objectstates/PATCH/ContentObjectStates.response.xml.example',
                    ],
                    'application/vnd.ibexa.api.ContentObjectStates+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentObjectStatesWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/objectstates/GET/ContentObjectStates.json.example',
                    ],
                ],
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The content item does not exist.',
            ],
        ],
    ),
)]
#[Patch(
    uriTemplate: '/content/objects/{contentId}/objectstates',
    name: 'Set Object states of content item',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Updates Object states of a content item. An Object state in the input overrides the state of the Object state group. PATCH or POST with header X-HTTP-Method-Override PATCH.',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the updated Object state is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The content item Object states input schema encoded in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'If-Match',
                in: 'header',
                required: true,
                description: 'ETag',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'contentId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.ContentObjectStates+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ContentObjectStates',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/objectstates/PATCH/ContentObjectStates.response.xml.example',
                ],
                'application/vnd.ibexa.api.ContentObjectStates+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ContentObjectStatesWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/objectstates/GET/ContentObjectStates.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'OK - Object state updated.',
                'content' => [
                    'application/vnd.ibexa.api.ContentObjectStates+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentObjectStates',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/objectstates/PATCH/ContentObjectStates.response.xml.example',
                    ],
                    'application/vnd.ibexa.api.ContentObjectStates+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentObjectStatesWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/objectstates/GET/ContentObjectStates.json.example',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - The input does not match the input schema definition.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to set an Object state.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - The input contains multiple Object states of the same Object state group.',
            ],
            Response::HTTP_PRECONDITION_FAILED => [
                'description' => 'Error - The current ETag does not match the one provided in the If-Match header.',
            ],
        ],
    ),
)]
#[Post(
    uriTemplate: '/content/objects/{contentId}/hide',
    name: 'Hide content item',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapiContext: ['requestBody' => false],
    openapi: new Model\Operation(
        summary: 'Makes or keep the content item invisible',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'contentId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'OK - Object item is hidden.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user has no permission to change Object item visibility.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The content item was not found.',
            ],
        ],
    ),
)]
#[Post(
    uriTemplate: '/content/objects/{contentId}/reveal',
    name: 'Reveal content item',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapiContext: ['requestBody' => false],
    openapi: new Model\Operation(
        summary: 'Makes or keep the content item visible',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'contentId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'OK - Object item is revealed.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user has no permission to change Object item visibility.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The content item was not found.',
            ],
        ],
    ),
)]
/**
 * Content controller.
 */
class Content extends RestController
{
    /**
     * Loads a content info by remote ID.
     *
     * @throws \Ibexa\Rest\Server\Exceptions\BadRequestException
     *
     * @return \Ibexa\Rest\Server\Values\TemporaryRedirect
     */
    public function redirectContent(Request $request)
    {
        if (!$request->query->has('remoteId')) {
            throw new BadRequestException("'remoteId' parameter is required.");
        }

        $contentInfo = $this->repository->getContentService()->loadContentInfoByRemoteId(
            $request->query->get('remoteId')
        );

        return new Values\TemporaryRedirect(
            $this->router->generate(
                'ibexa.rest.load_content',
                [
                    'contentId' => $contentInfo->id,
                ]
            )
        );
    }

    /**
     * Loads a content info, potentially with the current version embedded.
     *
     * @param mixed $contentId
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Ibexa\Rest\Server\Values\RestContent
     */
    public function loadContent($contentId, Request $request)
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);

        $mainLocation = null;
        if (!empty($contentInfo->mainLocationId)) {
            $mainLocation = $this->repository->getLocationService()->loadLocation($contentInfo->mainLocationId);
        }

        $contentType = $this->repository->getContentTypeService()->loadContentType($contentInfo->contentTypeId);

        $contentVersion = null;
        $relations = null;
        if ($this->getMediaType($request) === 'application/vnd.ibexa.api.content') {
            $languages = Language::ALL;
            if ($request->query->has('languages')) {
                $languages = explode(',', $request->query->get('languages'));
            }

            $contentVersion = $this->repository->getContentService()->loadContent($contentId, $languages);
            $relations = $this->repository->getContentService()->loadRelations($contentVersion->getVersionInfo());
        }

        $restContent = new Values\RestContent(
            $contentInfo,
            $mainLocation,
            $contentVersion,
            $contentType,
            $relations,
            $request->getPathInfo()
        );

        if ($contentInfo->mainLocationId === null) {
            return $restContent;
        }

        return new Values\CachedValue(
            $restContent,
            ['locationId' => $contentInfo->mainLocationId]
        );
    }

    /**
     * Updates a content's metadata.
     *
     * @param mixed $contentId
     *
     * @return \Ibexa\Rest\Server\Values\RestContent
     */
    public function updateContentMetadata($contentId, Request $request)
    {
        $updateStruct = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);

        // update section
        if ($updateStruct->sectionId !== null) {
            $section = $this->repository->getSectionService()->loadSection($updateStruct->sectionId);
            $this->repository->getSectionService()->assignSection($contentInfo, $section);
            $updateStruct->sectionId = null;
        }

        // @todo Consider refactoring! ContentService::updateContentMetadata throws the same exception
        // in case the updateStruct is empty and if remoteId already exists. Since REST version of update struct
        // includes section ID in addition to other fields, we cannot throw exception if only sectionId property
        // is set, so we must skip updating content in that case instead of allowing propagation of the exception.
        foreach ($updateStruct as $propertyName => $propertyValue) {
            if ($propertyName !== 'sectionId' && $propertyValue !== null) {
                // update content
                $this->repository->getContentService()->updateContentMetadata($contentInfo, $updateStruct);
                $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);
                break;
            }
        }

        try {
            $locationInfo = $this->repository->getLocationService()->loadLocation($contentInfo->mainLocationId);
        } catch (NotFoundException $e) {
            $locationInfo = null;
        }

        return new Values\RestContent(
            $contentInfo,
            $locationInfo
        );
    }

    /**
     * Loads a specific version of a given content object.
     *
     * @param mixed $contentId
     *
     * @return \Ibexa\Rest\Server\Values\TemporaryRedirect
     */
    public function redirectCurrentVersion($contentId)
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);

        return new Values\TemporaryRedirect(
            $this->router->generate(
                'ibexa.rest.load_content_in_version',
                [
                    'contentId' => $contentId,
                    'versionNumber' => $contentInfo->currentVersionNo,
                ]
            )
        );
    }

    /**
     * Loads a specific version of a given content object.
     *
     * @param mixed $contentId
     * @param int $versionNumber
     *
     * @return \Ibexa\Rest\Server\Values\Version
     */
    public function loadContentInVersion($contentId, $versionNumber, Request $request)
    {
        $languages = Language::ALL;
        if ($request->query->has('languages')) {
            $languages = explode(',', $request->query->get('languages'));
        }

        $content = $this->repository->getContentService()->loadContent(
            $contentId,
            $languages,
            $versionNumber
        );
        $contentType = $this->repository->getContentTypeService()->loadContentType(
            $content->getVersionInfo()->getContentInfo()->contentTypeId
        );

        $versionValue = new Values\Version(
            $content,
            $contentType,
            $this->repository->getContentService()->loadRelations($content->getVersionInfo()),
            $request->getPathInfo()
        );

        if ($content->contentInfo->mainLocationId === null || $content->versionInfo->status === VersionInfo::STATUS_DRAFT) {
            return $versionValue;
        }

        return new Values\CachedValue(
            $versionValue,
            ['locationId' => $content->contentInfo->mainLocationId]
        );
    }

    /**
     * Creates a new content draft assigned to the authenticated user.
     * If a different userId is given in the input it is assigned to the
     * given user but this required special rights for the authenticated
     * user (this is useful for content staging where the transfer process
     * does not have to authenticate with the user which created the content
     * object in the source server). The user has to publish the content if
     * it should be visible.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Ibexa\Rest\Server\Values\CreatedContent
     */
    public function createContent(Request $request)
    {
        $contentCreate = $this->parseContentRequest($request);

        return $this->doCreateContent($request, $contentCreate);
    }

    /**
     * The content is deleted. If the content has locations (which is required in 4.x)
     * on delete all locations assigned the content object are deleted via delete subtree.
     *
     * @param mixed $contentId
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function deleteContent($contentId)
    {
        $this->repository->getContentService()->deleteContent(
            $this->repository->getContentService()->loadContentInfo($contentId)
        );

        return new Values\NoContent();
    }

    /**
     * Creates a new content object as copy under the given parent location given in the destination header.
     *
     * @param mixed $contentId
     *
     * @return \Ibexa\Rest\Server\Values\ResourceCreated
     */
    public function copyContent($contentId, Request $request)
    {
        $destination = $request->headers->get('Destination');

        $parentLocationParts = explode('/', $destination);
        $copiedContent = $this->repository->getContentService()->copyContent(
            $this->repository->getContentService()->loadContentInfo($contentId),
            $this->repository->getLocationService()->newLocationCreateStruct(array_pop($parentLocationParts))
        );

        return new Values\ResourceCreated(
            $this->router->generate(
                'ibexa.rest.load_content',
                ['contentId' => $copiedContent->id]
            )
        );
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function copy(int $contentId, Request $request): Values\ResourceCreated
    {
        $contentService = $this->repository->getContentService();
        $locationService = $this->repository->getLocationService();

        $contentInfo = $contentService->loadContentInfo($contentId);

        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location $destinationLocation */
        $destinationLocation = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent(),
            ),
        );

        $copiedContent = $contentService->copyContent(
            $contentInfo,
            $locationService->newLocationCreateStruct($destinationLocation->getId()),
        );

        return new Values\ResourceCreated(
            $this->router->generate(
                'ibexa.rest.load_content',
                ['contentId' => $copiedContent->id],
            )
        );
    }

    /**
     * Deletes a translation from all the Versions of the given Content Object.
     *
     * If any non-published Version contains only the Translation to be deleted, that entire Version will be deleted
     *
     * @param int $contentId
     * @param string $languageCode
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     *
     * @throws \Exception
     */
    public function deleteContentTranslation($contentId, $languageCode)
    {
        $contentService = $this->repository->getContentService();

        $this->repository->beginTransaction();
        try {
            $contentInfo = $contentService->loadContentInfo($contentId);
            $contentService->deleteTranslation(
                $contentInfo,
                $languageCode
            );

            $this->repository->commit();

            return new Values\NoContent();
        } catch (\Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Returns a list of all versions of the content. This method does not
     * include fields and relations in the Version elements of the response.
     *
     * @param mixed $contentId
     *
     * @return \Ibexa\Rest\Server\Values\VersionList
     */
    public function loadContentVersions($contentId, Request $request)
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);

        return new Values\VersionList(
            $this->repository->getContentService()->loadVersions($contentInfo),
            $request->getPathInfo()
        );
    }

    /**
     * The version is deleted.
     *
     * @param mixed $contentId
     * @param mixed $versionNumber
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function deleteContentVersion($contentId, $versionNumber)
    {
        $versionInfo = $this->repository->getContentService()->loadVersionInfo(
            $this->repository->getContentService()->loadContentInfo($contentId),
            $versionNumber
        );

        if ($versionInfo->isPublished()) {
            throw new ForbiddenException('Versions with PUBLISHED status cannot be deleted');
        }

        $this->repository->getContentService()->deleteVersion(
            $versionInfo
        );

        return new Values\NoContent();
    }

    /**
     * Remove the given Translation from the given Version Draft.
     *
     * @param int $contentId
     * @param int $versionNumber
     * @param string $languageCode
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     */
    public function deleteTranslationFromDraft($contentId, $versionNumber, $languageCode)
    {
        $contentService = $this->repository->getContentService();
        $versionInfo = $contentService->loadVersionInfoById($contentId, $versionNumber);

        if (!$versionInfo->isDraft()) {
            throw new ForbiddenException('Translation can be deleted from a DRAFT version only');
        }

        $contentService->deleteTranslationFromDraft($versionInfo, $languageCode);

        return new Values\NoContent();
    }

    /**
     * The system creates a new draft version as a copy from the given version.
     *
     * @param mixed $contentId
     * @param mixed $versionNumber
     *
     * @return \Ibexa\Rest\Server\Values\CreatedVersion
     */
    public function createDraftFromVersion($contentId, $versionNumber)
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);
        $contentType = $this->repository->getContentTypeService()->loadContentType($contentInfo->contentTypeId);
        $contentDraft = $this->repository->getContentService()->createContentDraft(
            $contentInfo,
            $this->repository->getContentService()->loadVersionInfo($contentInfo, $versionNumber)
        );

        return new Values\CreatedVersion(
            [
                'version' => new Values\Version(
                    $contentDraft,
                    $contentType,
                    $this->repository->getContentService()->loadRelations($contentDraft->getVersionInfo())
                ),
            ]
        );
    }

    /**
     * The system creates a new draft version as a copy from the current version.
     *
     * @param mixed $contentId
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException if the current version is already a draft
     *
     * @return \Ibexa\Rest\Server\Values\CreatedVersion
     */
    public function createDraftFromCurrentVersion($contentId)
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);
        $contentType = $this->repository->getContentTypeService()->loadContentType($contentInfo->contentTypeId);
        $versionInfo = $this->repository->getContentService()->loadVersionInfo(
            $contentInfo
        );

        if ($versionInfo->isDraft()) {
            throw new ForbiddenException('Current version already has DRAFT status');
        }

        $contentDraft = $this->repository->getContentService()->createContentDraft($contentInfo);

        return new Values\CreatedVersion(
            [
                'version' => new Values\Version(
                    $contentDraft,
                    $contentType,
                    $this->repository->getContentService()->loadRelations($contentDraft->getVersionInfo())
                ),
            ]
        );
    }

    /**
     * A specific draft is updated.
     *
     * @param mixed $contentId
     * @param mixed $versionNumber
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     * @throws \Ibexa\Rest\Server\Exceptions\BadRequestException
     *
     * @return \Ibexa\Rest\Server\Values\Version
     */
    public function updateVersion($contentId, $versionNumber, Request $request)
    {
        $contentUpdateStruct = $this->inputDispatcher->parse(
            new Message(
                [
                    'Content-Type' => $request->headers->get('Content-Type'),
                    'Url' => $this->router->generate(
                        'ibexa.rest.update_version',
                        [
                            'contentId' => $contentId,
                            'versionNumber' => $versionNumber,
                        ]
                    ),
                ],
                $request->getContent()
            )
        );

        $versionInfo = $this->repository->getContentService()->loadVersionInfo(
            $this->repository->getContentService()->loadContentInfo($contentId),
            $versionNumber
        );

        if (!$versionInfo->isDraft()) {
            throw new ForbiddenException('Only versions with DRAFT status can be updated');
        }

        try {
            $this->repository->getContentService()->updateContent($versionInfo, $contentUpdateStruct);
        } catch (ContentValidationException $e) {
            throw new BadRequestException($e->getMessage());
        } catch (ContentFieldValidationException $e) {
            throw new RESTContentFieldValidationException($e);
        }

        $languages = null;
        if ($request->query->has('languages')) {
            $languages = explode(',', $request->query->get('languages'));
        }

        // Reload the content to handle languages GET parameter
        $content = $this->repository->getContentService()->loadContent(
            $contentId,
            $languages,
            $versionInfo->versionNo
        );
        $contentType = $this->repository->getContentTypeService()->loadContentType(
            $content->getVersionInfo()->getContentInfo()->contentTypeId
        );

        return new Values\Version(
            $content,
            $contentType,
            $this->repository->getContentService()->loadRelations($content->getVersionInfo()),
            $request->getPathInfo()
        );
    }

    /**
     * The content version is published.
     *
     * @param mixed $contentId
     * @param mixed $versionNumber
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException if version $versionNumber isn't a draft
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function publishVersion($contentId, $versionNumber)
    {
        $versionInfo = $this->repository->getContentService()->loadVersionInfo(
            $this->repository->getContentService()->loadContentInfo($contentId),
            $versionNumber
        );

        if (!$versionInfo->isDraft()) {
            throw new ForbiddenException('Only versions with DRAFT status can be published');
        }

        $this->repository->getContentService()->publishVersion(
            $versionInfo
        );

        return new Values\NoContent();
    }

    /**
     * Redirects to the relations of the current version.
     *
     * @param mixed $contentId
     *
     * @return \Ibexa\Rest\Server\Values\TemporaryRedirect
     */
    public function redirectCurrentVersionRelations($contentId)
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);

        return new Values\TemporaryRedirect(
            $this->router->generate(
                'ibexa.rest.redirect_current_version_relations',
                [
                    'contentId' => $contentId,
                    'versionNumber' => $contentInfo->currentVersionNo,
                ]
            )
        );
    }

    /**
     * Loads the relations of the given version.
     *
     * @param mixed $contentId
     * @param mixed $versionNumber
     *
     * @return \Ibexa\Rest\Server\Values\RelationList
     */
    public function loadVersionRelations($contentId, $versionNumber, Request $request)
    {
        $offset = $request->query->has('offset') ? (int)$request->query->get('offset') : 0;
        $limit = $request->query->has('limit') ? (int)$request->query->get('limit') : -1;

        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);
        $relationList = $this->repository->getContentService()->loadRelations(
            $this->repository->getContentService()->loadVersionInfo($contentInfo, $versionNumber)
        );

        $relationList = array_slice(
            $relationList,
            $offset >= 0 ? $offset : 0,
            $limit >= 0 ? $limit : null
        );

        $relationListValue = new Values\RelationList(
            $relationList,
            $contentId,
            $versionNumber,
            $request->getPathInfo()
        );

        if ($contentInfo->mainLocationId === null) {
            return $relationListValue;
        }

        return new Values\CachedValue(
            $relationListValue,
            ['locationId' => $contentInfo->mainLocationId]
        );
    }

    /**
     * Loads a relation for the given content object and version.
     *
     * @param mixed $contentId
     * @param int $versionNumber
     * @param mixed $relationId
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     *
     * @return \Ibexa\Rest\Server\Values\RestRelation
     */
    public function loadVersionRelation($contentId, $versionNumber, $relationId, Request $request)
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);
        $relationList = $this->repository->getContentService()->loadRelations(
            $this->repository->getContentService()->loadVersionInfo($contentInfo, $versionNumber)
        );

        foreach ($relationList as $relation) {
            if ($relation->id == $relationId) {
                $relation = new Values\RestRelation($relation, $contentId, $versionNumber);

                if ($contentInfo->mainLocationId === null) {
                    return $relation;
                }

                return new Values\CachedValue(
                    $relation,
                    ['locationId' => $contentInfo->mainLocationId]
                );
            }
        }

        throw new Exceptions\NotFoundException("Relation not found: '{$request->getPathInfo()}'.");
    }

    /**
     * Deletes a relation of the given draft.
     *
     * @param mixed $contentId
     * @param int   $versionNumber
     * @param mixed $relationId
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function removeRelation($contentId, $versionNumber, $relationId, Request $request)
    {
        $versionInfo = $this->repository->getContentService()->loadVersionInfo(
            $this->repository->getContentService()->loadContentInfo($contentId),
            $versionNumber
        );

        $versionRelations = $this->repository->getContentService()->loadRelations($versionInfo);
        foreach ($versionRelations as $relation) {
            if ($relation->id == $relationId) {
                if ($relation->type !== Relation::COMMON) {
                    throw new ForbiddenException('Relation is not of type COMMON');
                }

                if (!$versionInfo->isDraft()) {
                    throw new ForbiddenException('Relation of type COMMON can only be removed from drafts');
                }

                $this->repository->getContentService()->deleteRelation($versionInfo, $relation->getDestinationContentInfo());

                return new Values\NoContent();
            }
        }

        throw new Exceptions\NotFoundException("Could not find Relation '{$request->getPathInfo()}'.");
    }

    /**
     * Creates a new relation of type COMMON for the given draft.
     *
     * @param mixed $contentId
     * @param int $versionNumber
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException if version $versionNumber isn't a draft
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException if a relation to the same content already exists
     *
     * @return \Ibexa\Rest\Server\Values\CreatedRelation
     */
    public function createRelation($contentId, $versionNumber, Request $request)
    {
        $destinationContentId = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);
        $versionInfo = $this->repository->getContentService()->loadVersionInfo($contentInfo, $versionNumber);
        if (!$versionInfo->isDraft()) {
            throw new ForbiddenException('Relation of type COMMON can only be added to drafts');
        }

        try {
            $destinationContentInfo = $this->repository->getContentService()->loadContentInfo($destinationContentId);
        } catch (NotFoundException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }

        $existingRelations = $this->repository->getContentService()->loadRelations($versionInfo);
        foreach ($existingRelations as $existingRelation) {
            if ($existingRelation->getDestinationContentInfo()->id == $destinationContentId) {
                throw new ForbiddenException('Relation of type COMMON to the selected destination content ID already exists');
            }
        }

        $relation = $this->repository->getContentService()->addRelation($versionInfo, $destinationContentInfo);

        return new Values\CreatedRelation(
            [
                'relation' => new Values\RestRelation($relation, $contentId, $versionNumber),
            ]
        );
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function hideContent(int $contentId): Values\NoContent
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);

        $this->repository->getContentService()->hideContent($contentInfo);

        return new Values\NoContent();
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function revealContent(int $contentId): Values\NoContent
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);

        $this->repository->getContentService()->revealContent($contentInfo);

        return new Values\NoContent();
    }

    /**
     * Creates and executes a content view.
     *
     * @deprecated Since platform 1.0. Forwards the request to the new /views location, but returns a 301.
     *
     * @return \Ibexa\Rest\Server\Values\RestExecutedView
     */
    public function createView()
    {
        $response = $this->forward('ezpublish_rest.controller.views:createView');

        // Add 301 status code and location href
        $response->setStatusCode(301);
        $response->headers->set('Location', $this->router->generate('ibexa.rest.views.create'));

        return $response;
    }

    /**
     * @param string $controller
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function forward($controller)
    {
        $path['_controller'] = $controller;
        $subRequest = $this->container->get('request_stack')->getCurrentRequest()->duplicate(null, null, $path);

        return $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return mixed
     */
    protected function parseContentRequest(Request $request)
    {
        return $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type'), 'Url' => $request->getPathInfo()],
                $request->getContent()
            )
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Ibexa\Rest\Server\Values\RestContentCreateStruct $contentCreate
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     *
     * @return \Ibexa\Rest\Server\Values\CreatedContent
     */
    protected function doCreateContent(Request $request, RestContentCreateStruct $contentCreate)
    {
        try {
            $contentCreateStruct = $contentCreate->contentCreateStruct;
            $contentCreate->locationCreateStruct->sortField = $contentCreateStruct->contentType->defaultSortField;
            $contentCreate->locationCreateStruct->sortOrder = $contentCreateStruct->contentType->defaultSortOrder;

            $content = $this->repository->getContentService()->createContent(
                $contentCreateStruct,
                [$contentCreate->locationCreateStruct]
            );
        } catch (ContentValidationException $e) {
            throw new BadRequestException($e->getMessage());
        } catch (ContentFieldValidationException $e) {
            throw new RESTContentFieldValidationException($e);
        }

        $contentValue = null;
        $contentType = null;
        $relations = null;
        if ($this->getMediaType($request) === 'application/vnd.ibexa.api.content') {
            $contentValue = $content;
            $contentType = $this->repository->getContentTypeService()->loadContentType(
                $content->getVersionInfo()->getContentInfo()->contentTypeId
            );
            $relations = $this->repository->getContentService()->loadRelations($contentValue->getVersionInfo());
        }

        return new Values\CreatedContent(
            [
                'content' => new Values\RestContent(
                    $content->contentInfo,
                    null,
                    $contentValue,
                    $contentType,
                    $relations
                ),
            ]
        );
    }
}
