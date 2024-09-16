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
    uriTemplate: '/content/objects/{contentId}/versions/{versionNo}/translations/{languageCode}',
    name: 'Delete translation from version draft',
    openapi: new Model\Operation(
        summary: 'Removes a translation from a version draft.',
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
     * @param int $contentId
     * @param int $versionNumber
     * @param string $languageCode
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     */
    public function deleteTranslationFromDraft($contentId, $versionNumber, $languageCode)
    {
        $contentService = $this->repository->getContentService();
        $versionInfo = $contentService->loadVersionInfoById($contentId, $versionNumber);

        if (!$versionInfo->isDraft()) {
            throw new ForbiddenException('Translation can be deleted from a DRAFT version only');
        }

        $contentService->deleteTranslationFromDraft($versionInfo, $languageCode);

        return new Values\NoContent();
    }
}
