<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\ContentType;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values\ContentTypeGroupRefList;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/content/types/{contentTypeId}/groups',
    openapi: new Model\Operation(
        summary: 'Get groups of content type',
        description: 'Returns the content type group to which content type belongs to.',
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
class ContentTypeGroupListController extends RestController
{
    protected ContentTypeService $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Returns the content type groups the content type belongs to.
     */
    public function loadGroupsOfContentType(int $contentTypeId): ContentTypeGroupRefList
    {
        $contentType = $this->contentTypeService->loadContentType($contentTypeId, Language::ALL);

        return new ContentTypeGroupRefList(
            $contentType,
            $contentType->getContentTypeGroups()
        );
    }
}
