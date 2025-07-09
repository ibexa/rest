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
    uriTemplate: '/content/objects/{contentId}/versions/{versionNo}/translations/{languageCode}',
    openapi: new Model\Operation(
        summary: 'Delete translation from version draft',
        description: 'Removes a translation from a version draft.',
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
                'description' => 'No Content - removes a translation from a version draft.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to delete this translation.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - the version is not in draft state.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the content item or version number were not found.',
            ],
            Response::HTTP_NOT_ACCEPTABLE => [
                'description' => 'Error - the given translation does not exist for the version.',
            ],
            Response::HTTP_CONFLICT => [
                'description' => 'Error - the specified translation is the only one the version has or is the main translation.',
            ],
        ],
    ),
)]
class ContentDraftTranslationDeleteController extends RestController
{
    /**
     * Remove the given Translation from the given Version Draft.
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     */
    public function deleteTranslationFromDraft(int $contentId, ?int $versionNumber, string $languageCode): NoContent
    {
        $contentService = $this->repository->getContentService();
        $versionInfo = $contentService->loadVersionInfoById($contentId, $versionNumber);

        if (!$versionInfo->isDraft()) {
            throw new ForbiddenException('Translation can be deleted from a DRAFT version only');
        }

        $contentService->deleteTranslationFromDraft($versionInfo, $languageCode);

        return new NoContent();
    }
}
