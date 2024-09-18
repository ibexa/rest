<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Content;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\OpenApi\Model;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Response;

#[Delete(
    uriTemplate: '/content/objects/{contentId}',
    name: 'Delete Content',
    openapi: new Model\Operation(
        summary: 'Deletes content item. If content item has multiple Locations, all of them will be deleted via delete a subtree.',
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
        ],
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'The content item is deleted.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - content item was not found.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to delete this content item.',
            ],
        ],
    ),
)]
class ContentDeleteController extends RestController
{
    /**
     * The content is deleted. If the content has locations (which is required in 4.x)
     * on delete all locations assigned the content object are deleted via delete subtree.
     *
     * @param mixed $contentId
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function deleteContent($contentId)
    {
        $this->repository->getContentService()->deleteContent(
            $this->repository->getContentService()->loadContentInfo($contentId)
        );

        return new Values\NoContent();
    }
}
