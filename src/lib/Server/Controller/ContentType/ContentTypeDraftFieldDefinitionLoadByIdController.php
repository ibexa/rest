<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\ContentType;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values\RestFieldDefinition;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/content/types/{contentTypeId}/draft/fieldDefinitions/{fieldDefinitionId}',
    name: 'Get content type Draft Field definition',
    openapi: new Model\Operation(
        summary: 'Returns the Field definition by the given ID.',
        tags: [
            'Type',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the Field definition is returned in XML or JSON format.',
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
                name: 'fieldDefinitionId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - returns the Field definition.',
                'content' => [
                    'application/vnd.ibexa.api.FieldDefinition+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/FieldDefinition',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/field_definition_id/GET/FieldDefinition.xml.example',
                    ],
                    'application/vnd.ibexa.api.FieldDefinition+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/FieldDefinitionWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/field_definition_id/GET/FieldDefinition.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to read the content type draft.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The content type or draft does not exist.',
            ],
        ],
    ),
)]
class ContentTypeDraftFieldDefinitionLoadByIdController extends RestController
{
    protected ContentTypeService $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Returns the draft field definition given by id.
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     */
    public function loadContentTypeDraftFieldDefinition(
        int $contentTypeId,
        int $fieldDefinitionId,
        Request $request
    ): RestFieldDefinition {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($contentTypeId);

        foreach ($contentTypeDraft->getFieldDefinitions() as $fieldDefinition) {
            if ($fieldDefinition->id == $fieldDefinitionId) {
                return new RestFieldDefinition(
                    $contentTypeDraft,
                    $fieldDefinition
                );
            }
        }

        throw new Exceptions\NotFoundException("Field definition not found: '{$request->getPathInfo()}'.");
    }
}
