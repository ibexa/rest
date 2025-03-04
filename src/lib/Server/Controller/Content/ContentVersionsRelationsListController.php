<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Content;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
class ContentVersionsRelationsListController extends RestController
{
    public function __construct(
        private readonly ContentService\RelationListFacadeInterface $relationListFacade,
    ) {
    }

    /**
     * Loads the relations of the given version.
     */
    public function loadVersionRelations(int $contentId, int $versionNumber, Request $request): Values\CachedValue|Values\RelationList
    {
        $offset = $request->query->has('offset') ? (int)$request->query->get('offset') : 0;
        $limit = $request->query->has('limit') ? (int)$request->query->get('limit') : -1;

        $contentService = $this->repository->getContentService();

        $contentInfo = $contentService->loadContentInfo($contentId);

        $versionInfo = $contentService->loadVersionInfo($contentInfo, $versionNumber);
        $relationList = iterator_to_array($this->relationListFacade->getRelations($versionInfo));

        $relationList = array_slice(
            $relationList,
            $offset >= 0 ? $offset : 0,
            $limit >= 0 ? $limit : null
        );

        $relationListValue = new Values\RelationList(
            $relationList,
            $contentId,
            $versionNumber,
            $request->getPathInfo(),
        );

        if ($contentInfo->mainLocationId === null) {
            return $relationListValue;
        }

        return new Values\CachedValue(
            $relationListValue,
            ['locationId' => $contentInfo->mainLocationId]
        );
    }
}
