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
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup;
use Ibexa\Rest\Server\Controller as RestController;
use Symfony\Component\HttpFoundation\Response;

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
class ContentTypeGroupLoadByIdController extends RestController
{
    protected ContentTypeService $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Returns the content type group given by id.
     */
    public function loadContentTypeGroup(int $contentTypeGroupId): ContentTypeGroup
    {
        return $this->contentTypeService->loadContentTypeGroup($contentTypeGroupId, Language::ALL);
    }
}
