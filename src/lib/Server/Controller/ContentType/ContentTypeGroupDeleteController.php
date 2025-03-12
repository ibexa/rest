<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\ContentType;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Response;

#[Delete(
    uriTemplate: '/content/typegroups/{contentTypeGroupId}',
    openapi: new Model\Operation(
        summary: 'Delete content type group',
        description: 'Deletes the provided content type group.',
        tags: [
            'Type Groups',
        ],
        parameters: [
            new Model\Parameter(
                name: 'contentTypeGroupId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'No Content - content type group deleted.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to delete this content type group.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - The content type group is not empty.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The content type group does not exist.',
            ],
        ],
    ),
)]
class ContentTypeGroupDeleteController extends RestController
{
    protected ContentTypeService $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * The given content type group is deleted.
     *
     * @param mixed $contentTypeGroupId
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function deleteContentTypeGroup($contentTypeGroupId)
    {
        $contentTypeGroup = $this->contentTypeService->loadContentTypeGroup($contentTypeGroupId);

        $contentTypes = $this->contentTypeService->loadContentTypes($contentTypeGroup);
        if (!empty($contentTypes)) {
            throw new ForbiddenException('Only empty content type groups can be deleted');
        }

        $this->contentTypeService->deleteContentTypeGroup($contentTypeGroup);

        return new Values\NoContent();
    }
}
