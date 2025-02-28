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
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\BadRequestException;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Post(
    uriTemplate: '/content/types/{contentTypeId}/groups',
    name: 'Link group to content type',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Links a content type group to the content type and returns the updated group list.',
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
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - The input does not match the input schema definition.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to add a group.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - The content type is already assigned to the group.',
            ],
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject(),
        ),
    ),
)]
class ContentTypeLinkToGroupController extends RestController
{
    protected ContentTypeService $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Links a content type group to the content type and returns the updated group list.
     *
     * @param mixed $contentTypeId
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     * @throws \Ibexa\Rest\Server\Exceptions\BadRequestException
     *
     * @return \Ibexa\Rest\Server\Values\ContentTypeGroupRefList
     */
    public function linkContentTypeToGroup($contentTypeId, Request $request)
    {
        $contentType = $this->contentTypeService->loadContentType($contentTypeId);

        try {
            $contentTypeGroupId = $this->requestParser->parseHref(
                $request->query->get('group'),
                'contentTypeGroupId'
            );
        } catch (Exceptions\InvalidArgumentException $e) {
            // Group URI does not match the required value
            throw new BadRequestException($e->getMessage());
        }

        $contentTypeGroup = $this->contentTypeService->loadContentTypeGroup($contentTypeGroupId);

        $existingContentTypeGroups = $contentType->getContentTypeGroups();
        $contentTypeInGroup = false;
        foreach ($existingContentTypeGroups as $existingGroup) {
            if ($existingGroup->id == $contentTypeGroup->id) {
                $contentTypeInGroup = true;
                break;
            }
        }

        if ($contentTypeInGroup) {
            throw new ForbiddenException('The content type is already linked to the provided group');
        }

        $this->contentTypeService->assignContentTypeGroup(
            $contentType,
            $contentTypeGroup
        );

        $existingContentTypeGroups[] = $contentTypeGroup;

        return new Values\ContentTypeGroupRefList(
            $contentType,
            $existingContentTypeGroups
        );
    }
}
