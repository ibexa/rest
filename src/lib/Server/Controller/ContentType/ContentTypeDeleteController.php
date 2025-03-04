<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\ContentType;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\BadStateException;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Response;

#[Delete(
    uriTemplate: '/content/types/{contentTypeId}',
    name: 'Delete content type',
    openapi: new Model\Operation(
        summary: 'Deletes the provided content type.',
        tags: [
            'Type',
        ],
        parameters: [
            new Model\Parameter(
                name: 'contentTypeId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'No Content - content type deleted.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to delete this content type.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - There are object instances of this content type.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The content type does not exist.',
            ],
        ],
    ),
)]
class ContentTypeDeleteController extends RestController
{
    protected ContentTypeService $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * The given content type is deleted.
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     */
    public function deleteContentType(int $contentTypeId): Values\NoContent
    {
        $contentType = $this->contentTypeService->loadContentType($contentTypeId);

        try {
            $this->contentTypeService->deleteContentType($contentType);
        } catch (BadStateException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }

        return new Values\NoContent();
    }
}
