<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Content;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Values\Content\Relation;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values\NoContent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
class ContentVersionRelationDeleteController extends RestController
{
    public function __construct(
        private readonly ContentService\RelationListFacadeInterface $relationListFacade
    ) {
    }

    /**
     * Deletes a relation of the given draft.
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     */
    public function removeRelation(int $contentId, int $versionNumber, int $relationId, Request $request): NoContent
    {
        $contentService = $this->repository->getContentService();
        $versionInfo = $contentService->loadVersionInfo(
            $contentService->loadContentInfo($contentId),
            $versionNumber,
        );

        $versionRelations = iterator_to_array($this->relationListFacade->getRelations(
            $versionInfo,
        ));

        foreach ($versionRelations as $relation) {
            if ($relation->id == $relationId) {
                if ($relation->type !== Relation::COMMON) {
                    throw new ForbiddenException('Relation is not of type COMMON');
                }

                if (!$versionInfo->isDraft()) {
                    throw new ForbiddenException('Relation of type COMMON can only be removed from drafts');
                }

                $this->repository->getContentService()->deleteRelation($versionInfo, $relation->getDestinationContentInfo());

                return new NoContent();
            }
        }

        throw new Exceptions\NotFoundException("Could not find Relation '{$request->getPathInfo()}'.");
    }
}
