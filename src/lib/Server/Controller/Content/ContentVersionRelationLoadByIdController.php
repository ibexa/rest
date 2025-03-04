<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Content;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
class ContentVersionRelationLoadByIdController extends RestController
{
    public function __construct(
        private readonly ContentService\RelationListFacadeInterface $relationListFacade
    ) {
    }

    /**
     * Loads a relation for the given content object and version.
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     */
    public function loadVersionRelation(
        int $contentId,
        int $versionNumber,
        int $relationId,
        Request $request
    ): Values\CachedValue|Values\RestRelation {
        $contentService = $this->repository->getContentService();
        $contentInfo = $contentService->loadContentInfo($contentId);

        $relationList = iterator_to_array($this->relationListFacade->getRelations(
            $contentService->loadVersionInfo($contentInfo, $versionNumber),
        ));

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
}
