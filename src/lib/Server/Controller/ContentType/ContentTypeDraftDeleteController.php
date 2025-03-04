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
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Response;

#[Delete(
    uriTemplate: '/content/types/{contentTypeId}/draft',
    name: 'Delete content type draft',
    openapi: new Model\Operation(
        summary: 'Deletes the provided content type draft.',
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
                'description' => 'No Content - content type draft deleted.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to delete this content type draft.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The content type draft does not exist.',
            ],
        ],
    ),
)]
class ContentTypeDraftDeleteController extends RestController
{
    protected ContentTypeService $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * The given content type draft is deleted.
     */
    public function deleteContentTypeDraft(int $contentTypeId): Values\NoContent
    {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($contentTypeId);
        $this->contentTypeService->deleteContentType($contentTypeDraft);

        return new Values\NoContent();
    }
}
