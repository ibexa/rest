<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\ContentType;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values\FieldDefinitionList;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/content/types/{contentTypeId}/draft/fieldDefinitions',
    openapi: new Model\Operation(
        summary: 'Get Draft Field definition list',
        description: 'Returns all Field definitions of the provided content type Draft.',
        tags: [
            'Type',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the Field definitions are returned in XML or JSON format.',
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
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - return a list of Field definitions.',
                'content' => [
                    'application/vnd.ibexa.api.FieldDefinitionList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/FieldDefinitions',
                        ],
                    ],
                    'application/vnd.ibexa.api.FieldDefinitionList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/FieldDefinitionsWrapper',
                        ],
                    ],
                ],
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The content type draft does not exist.',
            ],
        ],
    ),
)]
class ContentTypeDraftFieldDefinitionListController extends RestController
{
    protected ContentTypeService $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Loads field definitions for a given content type draft.
     */
    public function loadContentTypeDraftFieldDefinitionList(int $contentTypeId): FieldDefinitionList
    {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($contentTypeId);

        return new FieldDefinitionList(
            $contentTypeDraft,
            $contentTypeDraft->getFieldDefinitions()->toArray(),
        );
    }
}
