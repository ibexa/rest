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
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/content/typegroups',
    name: 'Get content type groups',
    openapi: new Model\Operation(
        summary: 'Returns a list of all content type groups. If an identifier is provided, loads the content type group for this identifier.',
        tags: [
            'Type Groups',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the content type group list is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - returns a list of content type groups.',
                'content' => [
                    'application/vnd.ibexa.api.ContentTypeGroupList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeGroupList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/typegroups/GET/ContentTypeGroupList.xml.example',
                    ],
                    'application/vnd.ibexa.api.ContentTypeGroupList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentTypeGroupListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/typegroups/GET/ContentTypeGroupList.json.example',
                    ],
                ],
            ],
            Response::HTTP_TEMPORARY_REDIRECT => [
                'description' => 'Temporary redirect.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user has no permission to read content types.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The content type group with the given identifier does not exist.',
            ],
        ],
    ),
)]
class ContentTypeGroupsListController extends RestController
{
    protected ContentTypeService $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Returns a list of all content type groups.
     */
    public function loadContentTypeGroupList(Request $request): Values\TemporaryRedirect|\Ibexa\Rest\Server\Values\ContentTypeGroupList
    {
        if ($request->query->has('identifier')) {
            $contentTypeGroup = $this->contentTypeService->loadContentTypeGroupByIdentifier(
                $request->query->getString('identifier'),
            );

            return new Values\TemporaryRedirect(
                $this->router->generate(
                    'ibexa.rest.load_content_type_group',
                    [
                        'contentTypeGroupId' => $contentTypeGroup->id,
                    ]
                )
            );
        }

        $contentTypeGroupsIterable = $this->contentTypeService->loadContentTypeGroups(Language::ALL);
        $contentTypeGroups = [];
        foreach ($contentTypeGroupsIterable as $contentTypeGroup) {
            $contentTypeGroups[] = $contentTypeGroup;
        }

        return new Values\ContentTypeGroupList(
            $contentTypeGroups,
        );
    }
}
