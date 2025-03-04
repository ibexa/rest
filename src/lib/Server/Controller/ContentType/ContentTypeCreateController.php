<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\ContentType;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentTypeFieldDefinitionValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentTypeValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
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
    uriTemplate: '/content/typegroups/{contentTypeGroupId}/types',
    name: 'Create content type',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Creates a new content type draft in the given content type group.',
        tags: [
            'Type Groups',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the new content type or draft is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The content type Create schema encoded in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'contentTypeGroupId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.ContentTypeCreate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ContentTypeCreate',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/typegroups/content_type_group_id/types/POST/ContentTypeCreate.xml.example',
                ],
                'application/vnd.ibexa.api.ContentTypeCreate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ContentTypeCreateWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/typegroups/content_type_group_id/types/POST/ContentTypeCreate.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_CREATED => [
                'description' => 'Content type created.',
                'content' => [
                    'application/vnd.ibexa.api.ContentType+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentType',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/types/content_type_id/draft/PUBLISH/ContentType.xml.example',
                    ],
                    'application/vnd.ibexa.api.ContentType+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeWrapper',
                        ],
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - The input does not match the input schema definition. Validation on a Field definition fails. Validation of the content type fails, e.g. multiple Fields of a same singular Field Type are provided. Publish is set to true and the input is not complete e.g. no Field definitions are provided.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to create this content type.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - A content type with same identifier already exists.',
            ],
        ],
    ),
)]
class ContentTypeCreateController extends RestController
{
    protected ContentTypeService $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Creates a new content type draft in the given content type group.
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     * @throws \Ibexa\Rest\Server\Exceptions\BadRequestException
     */
    public function createContentType(int $contentTypeGroupId, Request $request): Values\CreatedContentType
    {
        $contentTypeGroup = $this->contentTypeService->loadContentTypeGroup($contentTypeGroupId);
        $publish = ($request->query->has('publish') && $request->query->get('publish') === 'true');

        try {
            $contentTypeDraft = $this->contentTypeService->createContentType(
                $this->inputDispatcher->parse(
                    new Message(
                        [
                            'Content-Type' => $request->headers->get('Content-Type'),
                            // @todo Needs refactoring! Temporary solution so parser has access to get parameters
                            '__publish' => $publish,
                        ],
                        $request->getContent()
                    )
                ),
                [$contentTypeGroup]
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        } catch (ContentTypeValidationException $e) {
            throw new BadRequestException($e->getMessage());
        } catch (ContentTypeFieldDefinitionValidationException $e) {
            throw new BadRequestException($e->getMessage());
        } catch (Exceptions\Parser $e) {
            throw new BadRequestException($e->getMessage());
        }

        if ($publish) {
            $this->contentTypeService->publishContentTypeDraft($contentTypeDraft);

            $contentType = $this->contentTypeService->loadContentType($contentTypeDraft->id, Language::ALL);

            return new Values\CreatedContentType(
                [
                    'contentType' => new Values\RestContentType(
                        $contentType,
                        $contentType->getFieldDefinitions()->toArray()
                    ),
                ]
            );
        }

        return new Values\CreatedContentType(
            [
                'contentType' => new Values\RestContentType(
                    $contentTypeDraft,
                    $contentTypeDraft->getFieldDefinitions()->toArray()
                ),
            ]
        );
    }
}
