<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\ContentType;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Response;

#[Delete(
    uriTemplate: '/content/types/{contentTypeId}/groups/{id}',
    openapi: new Model\Operation(
        summary: 'Unlink group from content type',
        description: 'Removes the given group from the content type and returns the updated group list.',
        tags: [
            'Type',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the updated content type group list is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'contentTypeId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'content' => [
                    'application/vnd.ibexa.api.ContentTypeGroupRefList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeGroupRefList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/groups/id/DELETE/ContentTypeGroupRefList.xml.example',
                    ],
                    'application/vnd.ibexa.api.ContentTypeGroupRefList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeGroupRefListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/groups/id/DELETE/ContentTypeGroupRefList.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to delete this content type.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - content type cannot be unlinked from the only remaining group.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The resource does not exist.',
            ],
        ],
    ),
)]
class ContentTypeUnlinkFromGroupController extends RestController
{
    protected ContentTypeService $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Removes the given group from the content type and returns the updated group list.
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     */
    public function unlinkContentTypeFromGroup(int $contentTypeId, int $contentTypeGroupId): \Ibexa\Rest\Server\Values\ContentTypeGroupRefList
    {
        $contentType = $this->contentTypeService->loadContentType($contentTypeId);
        $contentTypeGroup = $this->contentTypeService->loadContentTypeGroup($contentTypeGroupId);

        $existingContentTypeGroups = $contentType->getContentTypeGroups();
        $contentTypeInGroup = false;
        foreach ($existingContentTypeGroups as $existingGroup) {
            if ($existingGroup->id == $contentTypeGroup->id) {
                $contentTypeInGroup = true;
                break;
            }
        }

        if (!$contentTypeInGroup) {
            throw new Exceptions\NotFoundException('The content type is not in the provided group');
        }

        if (count($existingContentTypeGroups) == 1) {
            throw new ForbiddenException('Cannot unlink the content type from its only remaining group');
        }

        $this->contentTypeService->unassignContentTypeGroup(
            $contentType,
            $contentTypeGroup
        );

        $contentType = $this->contentTypeService->loadContentType($contentTypeId);

        return new Values\ContentTypeGroupRefList(
            $contentType,
            $contentType->getContentTypeGroups()
        );
    }
}
