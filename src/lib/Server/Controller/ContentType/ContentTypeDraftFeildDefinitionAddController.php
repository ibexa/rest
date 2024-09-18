<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\ContentType;

use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\BadStateException;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentTypeFieldDefinitionValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\BadRequestException;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Post(
    uriTemplate: '/content/types/{contentTypeId}/draft/fieldDefinitions',
    name: 'Add content type Draft Field definition',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Creates a new Field definition for the given content type.',
        tags: [
            'Type',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the new Field definition is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The Field Definition Create schema encoded in XML or JSON format.',
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
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.FieldDefinitionCreate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/FieldDefinitionCreate',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/draft/field_definitions/POST/FieldDefinitionCreate.xml.example',
                ],
                'application/vnd.ibexa.api.FieldDefinitionCreate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/FieldDefinitionCreateWrapper',
                    ],
                ],
            ]),
        ),
        responses: [
            Response::HTTP_CREATED => [
                'description' => 'Field definition created.',
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
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - The input does not match the input schema definition or validation on the Field definition fails.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to add a Field definition.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - A Field definition with the same identifier already exists in the given content type. The Field definition is of singular type, already existing in the given content type. The Field definition you want to add is of a type that can\'t be added to a content type that already has content instances.',
            ],
        ],
    ),
)]
class ContentTypeDraftFeildDefinitionAddController extends RestController
{
    protected ContentTypeService $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Creates a new field definition for the given content type draft.
     *
     * @param $contentTypeId
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     *
     * @return \Ibexa\Rest\Server\Values\CreatedFieldDefinition
     */
    public function addContentTypeDraftFieldDefinition($contentTypeId, Request $request)
    {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($contentTypeId);
        $fieldDefinitionCreate = $this->inputDispatcher->parse(
            new Message(
                [
                    'Content-Type' => $request->headers->get('Content-Type'),
                ],
                $request->getContent()
            )
        );

        try {
            $this->contentTypeService->addFieldDefinition(
                $contentTypeDraft,
                $fieldDefinitionCreate
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        } catch (ContentTypeFieldDefinitionValidationException $e) {
            throw new BadRequestException($e->getMessage());
        } catch (BadStateException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }

        $updatedDraft = $this->contentTypeService->loadContentTypeDraft($contentTypeId);
        foreach ($updatedDraft->getFieldDefinitions() as $fieldDefinition) {
            if ($fieldDefinition->identifier == $fieldDefinitionCreate->identifier) {
                return new Values\CreatedFieldDefinition(
                    [
                        'fieldDefinition' => new Values\RestFieldDefinition($updatedDraft, $fieldDefinition),
                    ]
                );
            }
        }

        throw new Exceptions\NotFoundException("Field definition not found: '{$request->getPathInfo()}'.");
    }
}
