<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Content;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\OpenApi\Model;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values\NoContent;
use Symfony\Component\HttpFoundation\Response;

#[Delete(
    uriTemplate: '/content/objects/{contentId}/versions/{versionNo}',
    openapi: new Model\Operation(
        summary: 'Delete content version',
        description: 'Deletes the content version.',
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
class ContentVersionDeleteController extends RestController
{
    /**
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     */
    public function deleteContentVersion(int $contentId, ?int $versionNumber): NoContent
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

        return new NoContent();
    }
}
