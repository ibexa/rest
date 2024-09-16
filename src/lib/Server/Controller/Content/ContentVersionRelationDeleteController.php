<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Content;

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
}
