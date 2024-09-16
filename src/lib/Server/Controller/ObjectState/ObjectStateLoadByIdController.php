<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\ObjectState;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values;
use Ibexa\Rest\Values\ContentObjectStates;
use Ibexa\Rest\Values\RestObjectState;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/content/objectstategroups/{objectStateGroupId}/objectstates/{objectStateId}',
    name: 'Get Object state',
    openapi: new Model\Operation(
        summary: 'Returns the Object state.',
        tags: [
            'Object State Groups',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the Object State is returned in XML or JSON format.',
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
            new Model\Parameter(
                name: 'objectStateId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - returns the Object state.',
                'content' => [
                    'application/vnd.ibexa.api.ObjectState+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ObjectState',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objectstategroups/object_state_group_id/objectstates/object_state_id/PATCH/ObjectState.xml.example',
                    ],
                    'application/vnd.ibexa.api.ObjectState+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ObjectStateWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objectstategroups/object_state_group_id/objectstates/object_state_id/PATCH/ObjectState.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to read this Object state.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The Object state does not exist.',
            ],
        ],
    ),
)]
class ObjectStateLoadByIdController extends RestController
{
    protected ObjectStateService $objectStateService;

    protected ContentService $contentService;

    public function __construct(ObjectStateService $objectStateService, ContentService $contentService)
    {
        $this->objectStateService = $objectStateService;
        $this->contentService = $contentService;
    }

    /**
     * Loads an object state.
     *
     * @param $objectStateGroupId
     * @param $objectStateId
     *
     * @return \Ibexa\Rest\Values\RestObjectState
     */
    public function loadObjectState($objectStateGroupId, $objectStateId)
    {
        return new RestObjectState(
            $this->objectStateService->loadObjectState($objectStateId, Language::ALL),
            $objectStateGroupId
        );
    }
}
