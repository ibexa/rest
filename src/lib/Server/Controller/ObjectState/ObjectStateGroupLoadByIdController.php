<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\ObjectState;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Rest\Server\Controller as RestController;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/content/objectstategroups/{objectStateGroupId}',
    openapi: new Model\Operation(
        summary: 'Get Object state group',
        description: 'Returns the Object state group with the provided ID.',
        tags: [
            'Object State Groups',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the Object state group is returned in XML or JSON format.',
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
            new Model\Parameter(
                name: 'objectStateGroupId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - returns the Object state group.',
                'content' => [
                    'application/vnd.ibexa.api.ObjectStateGroup+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ObjectStateGroup',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objectstategroups/object_state_group_id/PATCH/ObjectStateGroup.xml.example',
                    ],
                    'application/vnd.ibexa.api.ObjectStateGroup+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ObjectStateGroupWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objectstategroups/object_state_group_id/PATCH/ObjectStateGroup.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to read this Object state group.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The Object state group does not exist.',
            ],
        ],
    ),
)]
class ObjectStateGroupLoadByIdController extends RestController
{
    protected ObjectStateService $objectStateService;

    protected ContentService $contentService;

    public function __construct(ObjectStateService $objectStateService, ContentService $contentService)
    {
        $this->objectStateService = $objectStateService;
        $this->contentService = $contentService;
    }

    /**
     * Loads an object state group.
     */
    public function loadObjectStateGroup(int $objectStateGroupId): \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup
    {
        return $this->objectStateService->loadObjectStateGroup($objectStateGroupId, Language::ALL);
    }
}
