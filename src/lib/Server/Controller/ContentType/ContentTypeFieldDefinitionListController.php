<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\ContentType;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values\FieldDefinitionList;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/content/types/{contentTypeId}/fieldDefinitions',
    name: 'Get Field definition list',
    openapi: new Model\Operation(
        summary: 'Returns all Field definitions of the provided content type.',
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
                'description' => 'Error - The content type does not exist.',
            ],
        ],
    ),
)]
class ContentTypeFieldDefinitionListController extends RestController
{
    protected ContentTypeService $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Loads field definitions for a given content type.
     *
     * @todo Check why this isn't in the specs
     */
    public function loadContentTypeFieldDefinitionList(int $contentTypeId): FieldDefinitionList
    {
        $contentType = $this->contentTypeService->loadContentType($contentTypeId, Language::ALL);

        return new FieldDefinitionList(
            $contentType,
            $contentType->getFieldDefinitions()->toArray()
        );
    }
}
