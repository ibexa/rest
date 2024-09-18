<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Content;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
class ContentLoadByIdController extends RestController
{
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
}
