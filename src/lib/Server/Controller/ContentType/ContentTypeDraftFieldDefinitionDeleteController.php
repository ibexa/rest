<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\ContentType;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values\NoContent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Delete(
    uriTemplate: '/content/types/{contentTypeId}/draft/fieldDefinitions/{fieldDefinitionId}',
    name: 'Delete content type Draft Field definition',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Deletes the provided Field definition.',
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
            new Model\Parameter(
                name: 'fieldDefinitionId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'No Content - Field definition deleted.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to delete this content type.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - There is no draft of the content type assigned to the authenticated user.',
            ],
        ],
    ),
)]
class ContentTypeDraftFieldDefinitionDeleteController extends RestController
{
    protected ContentTypeService $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Deletes a field definition from a content type draft.
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     */
    public function removeContentTypeDraftFieldDefinition(
        int $contentTypeId,
        int $fieldDefinitionId,
        Request $request,
    ): NoContent {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($contentTypeId);

        $fieldDefinition = null;
        foreach ($contentTypeDraft->getFieldDefinitions() as $fieldDef) {
            if ($fieldDef->id == $fieldDefinitionId) {
                $fieldDefinition = $fieldDef;
            }
        }

        if ($fieldDefinition === null) {
            throw new Exceptions\NotFoundException("Field definition not found: '{$request->getPathInfo()}'.");
        }

        $this->contentTypeService->removeFieldDefinition(
            $contentTypeDraft,
            $fieldDefinition
        );

        return new NoContent();
    }
}
