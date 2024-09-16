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
    uriTemplate: '/content/objects/{contentId}/translations/{languageCode}',
    name: 'Delete translation (permanently)',
    openapi: new Model\Operation(
        summary: 'Permanently deletes a translation from all versions of a content item.',
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
     * @param int $contentId
     * @param string $languageCode
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     *
     * @throws \Exception
     */
    public function deleteContentTranslation($contentId, $languageCode)
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

            return new Values\NoContent();
        } catch (\Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
    }
}
