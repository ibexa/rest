<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\ContentType;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Response;

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
class ContentTypeDraftLoadController extends RestController
{
    protected ContentTypeService $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
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
}
