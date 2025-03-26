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
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Post(
    uriTemplate: '/content/typegroups',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Create content type group',
        description: 'Creates a new content type group.',
        tags: [
            'Type Groups',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the new content type group is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The content type group input schema encoded in XML or JSON.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.ContentTypeGroupInput+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ContentTypeGroupInput',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/typegroups/POST/ContentTypeGroupInput.xml.example',
                ],
                'application/vnd.ibexa.api.ContentTypeGroupInput+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ContentTypeGroupInputWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/typegroups/POST/ContentTypeGroupInput.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_CREATED => [
                'description' => 'Content type group created.',
                'content' => [
                    'application/vnd.ibexa.api.ContentTypeGroup+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeGroup',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/typegroups/POST/ContentTypeGroup.xml.example',
                    ],
                    'application/vnd.ibexa.api.ContentTypeGroup+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeGroupWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/typegroups/content_type_group_id/PATCH/ContentTypeGroup.json.example',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - The input does not match the input schema definition.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to create this content type group.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - A content type group with the same identifier already exists.',
            ],
        ],
    ),
)]
class ContentTypeGroupCreateController extends RestController
{
    protected ContentTypeService $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Creates a new content type group.
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     */
    public function createContentTypeGroup(Request $request): Values\CreatedContentTypeGroup
    {
        $createStruct = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent(),
            )
        );

        try {
            return new Values\CreatedContentTypeGroup(
                [
                    'contentTypeGroup' => $this->contentTypeService->createContentTypeGroup($createStruct),
                ],
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }
    }
}
