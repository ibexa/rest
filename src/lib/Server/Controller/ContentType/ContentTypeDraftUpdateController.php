<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\ContentType;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values;
use Ibexa\Rest\Server\Values\RestContentType;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Patch(
    uriTemplate: '/content/types/{contentTypeId}/draft',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Update content type draft',
        description: 'Updates metadata of a draft. This method does not handle Field definitions. PATCH or POST with header X-HTTP-Method-Override PATCH.',
        tags: [
            'Type',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the new content type draft is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The content type update schema encoded in XML or JSON format.',
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
                'application/vnd.ibexa.api.ContentTypeUpdate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ContentTypeUpdate',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/POST/ContentTypeUpdate.xml.example',
                ],
                'application/vnd.ibexa.api.ContentTypeUpdate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ContentTypeUpdateWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/POST/ContentTypeUpdate.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_OK => [
                'description' => 'Draft metadata updated.',
                'content' => [
                    'application/vnd.ibexa.api.ContentTypeInfo+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeInfo',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/draft/PATCH/ContentTypeInfo.xml.example',
                    ],
                    'application/vnd.ibexa.api.ContentTypeInfo+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeInfoWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/draft/PATCH/ContentTypeInfo.json.example',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - The input does not match the input schema definition.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to update the draft.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - A content type with the given new identifier already exists.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - There is no draft for this content type.',
            ],
        ],
    ),
)]
class ContentTypeDraftUpdateController extends RestController
{
    protected ContentTypeService $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Updates metadata of a draft. This method does not handle field definitions.
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     */
    public function updateContentTypeDraft(int $contentTypeId, Request $request): RestContentType
    {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($contentTypeId);
        $contentTypeUpdateStruct = $this->inputDispatcher->parse(
            new Message(
                [
                    'Content-Type' => $request->headers->get('Content-Type'),
                ],
                $request->getContent()
            )
        );

        try {
            $this->contentTypeService->updateContentTypeDraft(
                $contentTypeDraft,
                $contentTypeUpdateStruct
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }

        return new RestContentType(
            // Reload the content type draft to get the updated values
            $this->contentTypeService->loadContentTypeDraft(
                $contentTypeDraft->id,
            )
        );
    }
}
