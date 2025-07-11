<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\ContentType;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values\ContentTypeInfoList;
use Ibexa\Rest\Server\Values\ContentTypeList;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/content/typegroups/{contentTypeGroupId}/types',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'List content types for group',
        description: 'Returns a list of content types in the provided group.',
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
class ContentTypeListForGroupController extends RestController
{
    protected ContentTypeService $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Returns a list of content types of the group.
     */
    public function listContentTypesForGroup(int $contentTypeGroupId, Request $request): ContentTypeList|ContentTypeInfoList
    {
        $contentTypesIterable = $this->contentTypeService->loadContentTypes(
            $this->contentTypeService->loadContentTypeGroup($contentTypeGroupId, Language::ALL),
            Language::ALL,
        );
        $contentTypes = [];
        foreach ($contentTypesIterable as $contentType) {
            $contentTypes[] = $contentType;
        }

        if ($this->getMediaType($request) === 'application/vnd.ibexa.api.contenttypelist') {
            return new ContentTypeList($contentTypes, $request->getPathInfo());
        }

        return new ContentTypeInfoList($contentTypes, $request->getPathInfo());
    }
}
