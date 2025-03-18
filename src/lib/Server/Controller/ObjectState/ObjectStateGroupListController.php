<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\ObjectState;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values\ObjectStateGroupList;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/content/objectstategroups',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'List Object state groups',
        description: 'Returns a list of all Object state groups.',
        tags: [
            'Object State Groups',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the Object state group list is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'If-None-Match',
                in: 'header',
                required: true,
                description: 'ETag',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - returns a list of Object state groups.',
                'content' => [
                    'application/vnd.ibexa.api.ObjectStateGroupList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ObjectStateGroupList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objectstategroups/GET/ObjectStateGroupList.xml.example',
                    ],
                    'application/vnd.ibexa.api.ObjectStateGroupList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ObjectStateGroupListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objectstategroups/GET/ObjectStateGroupList.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user has no permission to read Object state groups.',
            ],
        ],
    ),
)]
class ObjectStateGroupListController extends RestController
{
    protected ObjectStateService $objectStateService;

    protected ContentService $contentService;

    public function __construct(ObjectStateService $objectStateService, ContentService $contentService)
    {
        $this->objectStateService = $objectStateService;
        $this->contentService = $contentService;
    }

    /**
     * Returns a list of all object state groups.
     *
     * @return \Ibexa\Rest\Server\Values\ObjectStateGroupList
     */
    public function loadObjectStateGroups(): ObjectStateGroupList
    {
        $objectStateGroupsIterable = $this->objectStateService->loadObjectStateGroups(0, -1, Language::ALL);
        $objectStateGroups = [];
        foreach ($objectStateGroupsIterable as $objectStateGroup) {
            $objectStateGroups[] = $objectStateGroup;
        }

        return new ObjectStateGroupList(
            $objectStateGroups,
        );
    }
}
