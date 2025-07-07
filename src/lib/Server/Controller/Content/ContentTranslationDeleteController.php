<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Content;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\OpenApi\Model;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values\NoContent;
use Symfony\Component\HttpFoundation\Response;

#[Delete(
    uriTemplate: '/content/objects/{contentId}/translations/{languageCode}',
    openapi: new Model\Operation(
        summary: 'Delete translation (permanently)',
        description: 'Permanently deletes a translation from all versions of a content item.',
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
                name: 'languageCode',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'No Content',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to delete content item (content/remove policy).',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the content item was not found.',
            ],
            Response::HTTP_NOT_ACCEPTABLE => [
                'description' => 'Error - the given translation does not exist for the content item.',
            ],
            Response::HTTP_CONFLICT => [
                'description' => 'Error - the specified translation is the only one any version has or is the main translation.',
            ],
        ],
    ),
)]
class ContentTranslationDeleteController extends RestController
{
    /**
     * Deletes a translation from all the Versions of the given Content Object.
     *
     * If any non-published Version contains only the Translation to be deleted, that entire Version will be deleted
     *
     * @throws \Exception
     */
    public function deleteContentTranslation(int $contentId, string $languageCode): NoContent
    {
        $contentService = $this->repository->getContentService();

        $this->repository->beginTransaction();
        try {
            $contentInfo = $contentService->loadContentInfo($contentId);
            $contentService->deleteTranslation(
                $contentInfo,
                $languageCode
            );

            $this->repository->commit();

            return new NoContent();
        } catch (\Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
    }
}
