<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller;

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
    uriTemplate: '/content/objectstategroups',
    name: 'List Object state groups',
    openapi: new Model\Operation(
        summary: 'Returns a list of all Object state groups.',
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
#[Post(
    uriTemplate: '/content/objectstategroups',
    name: 'Create Object state group',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Creates a new Object state group.',
        tags: [
            'Object State Groups',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the new Object state group is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The Object state group input schema encoded in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.ObjectStateGroupCreate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ObjectStateGroupCreate',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objectstategroups/POST/ObjectStateGroupCreate.xml.example',
                ],
                'application/vnd.ibexa.api.ObjectStateGroupCreate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ObjectStateGroupCreateWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objectstategroups/POST/ObjectStateGroupCreate.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_CREATED => [
                'description' => 'Object state group created.',
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
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - The input does not match the input schema definition.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to create an Object state group.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - An Object state group with the same identifier already exists.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/content/objectstategroups/{objectStateGroupId}',
    name: 'Get Object state group',
    openapi: new Model\Operation(
        summary: 'Returns the Object state group with the provided ID.',
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
#[Patch(
    uriTemplate: '/content/objectstategroups/{objectStateGroupId}',
    name: 'Update Object state group',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Updates an Object state group. PATCH or POST with header X-HTTP-Method-Override PATCH.',
        tags: [
            'Object State Groups',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the updated Object state group is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The Object state group input schema encoded in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'If-Match',
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
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.ObjectStateGroupUpdate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ObjectStateGroupUpdate',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objectstategroups/object_state_group_id/PATCH/ObjectStateGroupUpdate.xml.example',
                ],
                'application/vnd.ibexa.api.ObjectStateGroupUpdate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ObjectStateGroupUpdateWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objectstategroups/object_state_group_id/PATCH/ObjectStateGroupUpdate.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - Object stated group updated.',
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
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - The input does not match the input schema definition.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to update an Object state group.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - An Object state group with the provided identifier already exists.',
            ],
            Response::HTTP_PRECONDITION_FAILED => [
                'description' => 'Error - The current ETag does not match the one provided in the If-Match header.',
            ],
        ],
    ),
)]
#[Delete(
    uriTemplate: '/content/objectstategroups/{objectStateGroupId}',
    name: 'Delete Object state group',
    openapi: new Model\Operation(
        summary: 'Deletes the given Object state group including Object states.',
        tags: [
            'Object State Groups',
        ],
        parameters: [
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
            Response::HTTP_NO_CONTENT => [
                'description' => 'No Content - Object state group deleted.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to delete an Object state group.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The Object state group does not exist.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/content/objectstategroups/{objectStateGroupId}/objectstates',
    name: 'List Object states',
    openapi: new Model\Operation(
        summary: 'Returns a list of all Object states of the given group.',
        tags: [
            'Object State Groups',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the Object state list is returned in XML or JSON format.',
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
                'description' => 'OK - returns a list of Object states.',
                'content' => [
                    'application/vnd.ibexa.api.ObjectStateList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ObjectStateList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objectstategroups/object_state_group_id/objectstates/GET/ObjectStateList.xml.example',
                    ],
                    'application/vnd.ibexa.api.ObjectStateList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ObjectStateListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objectstategroups/object_state_group_id/objectstates/GET/ObjectStateList.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user has no permission to read Object states.',
            ],
        ],
    ),
)]
#[Post(
    uriTemplate: '/content/objectstategroups/{objectStateGroupId}/objectstates',
    name: 'Create Object state',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Creates a new Object state.',
        tags: [
            'Object State Groups',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the new Object state is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The Object state input schema encoded in XML or JSON format.',
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
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.ObjectStateCreate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ObjectStateCreate',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objectstategroups/object_state_group_id/objectstates/POST/ObjectStateCreate.xml.example',
                ],
                'application/vnd.ibexa.api.ObjectStateCreate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ObjectStateCreateWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objectstategroups/object_state_group_id/objectstates/POST/ObjectStateCreate.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_CREATED => [
                'description' => 'Object state created.',
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
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - The input does not match the input schema definition.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to create an Object state.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - An Object state with the same identifier already exists in the given group.',
            ],
        ],
    ),
)]
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
#[Patch(
    uriTemplate: '/content/objectstategroups/{objectStateGroupId}/objectstates/{objectStateId}',
    name: 'Update Object state',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Updates an Object state. PATCH or POST with header X-HTTP-Method-Override PATCH.',
        tags: [
            'Object State Groups',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the updated Object state is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The Object state input schema encoded in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'If-Match',
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
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.ObjectStateUpdate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ObjectStateUpdate',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objectstategroups/object_state_group_id/objectstates/object_state_id/PATCH/ObjectStateUpdate.xml.example',
                ],
                'application/vnd.ibexa.api.ObjectStateUpdate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ObjectStateUpdateWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objectstategroups/object_state_group_id/objectstates/object_state_id/PATCH/ObjectStateUpdate.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - Object State updated',
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
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - The input does not match the input schema definition.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to update the Object state.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - An Object state with the provided identifier already exists in this group.',
            ],
            Response::HTTP_PRECONDITION_FAILED => [
                'description' => 'Error - The current ETag does not match the one provided in the If-Match header.',
            ],
        ],
    ),
)]
#[Delete(
    uriTemplate: '/content/objectstategroups/{objectStateGroupId}/objectstates/{objectStateId}',
    name: 'Delete Object state',
    openapi: new Model\Operation(
        summary: 'Deletes provided Object state.',
        tags: [
            'Object State Groups',
        ],
        parameters: [
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
            Response::HTTP_NO_CONTENT => [
                'description' => 'No Content - Object state deleted.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to delete an Object state.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The Object state does not exist.',
            ],
        ],
    ),
)]
/**
 * ObjectState controller.
 */
class ObjectState extends RestController
{
    /**
     * ObjectState service.
     *
     * @var \Ibexa\Contracts\Core\Repository\ObjectStateService
     */
    protected $objectStateService;

    /**
     * Content service.
     *
     * @var \Ibexa\Contracts\Core\Repository\ContentService
     */
    protected $contentService;

    /**
     * Construct controller.
     *
     * @param \Ibexa\Contracts\Core\Repository\ObjectStateService $objectStateService
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentService
     */
    public function __construct(ObjectStateService $objectStateService, ContentService $contentService)
    {
        $this->objectStateService = $objectStateService;
        $this->contentService = $contentService;
    }

    /**
     * Creates a new object state group.
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     *
     * @return \Ibexa\Rest\Server\Values\CreatedObjectStateGroup
     */
    public function createObjectStateGroup(Request $request)
    {
        try {
            $createdStateGroup = $this->objectStateService->createObjectStateGroup(
                $this->inputDispatcher->parse(
                    new Message(
                        ['Content-Type' => $request->headers->get('Content-Type')],
                        $request->getContent()
                    )
                )
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */$e->getMessage());
        }

        return new Values\CreatedObjectStateGroup(
            [
                'objectStateGroup' => $createdStateGroup,
            ]
        );
    }

    /**
     * Creates a new object state.
     *
     * @param $objectStateGroupId
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     *
     * @return \Ibexa\Rest\Server\Values\CreatedObjectState
     */
    public function createObjectState($objectStateGroupId, Request $request)
    {
        $objectStateGroup = $this->objectStateService->loadObjectStateGroup($objectStateGroupId);

        try {
            $createdObjectState = $this->objectStateService->createObjectState(
                $objectStateGroup,
                $this->inputDispatcher->parse(
                    new Message(
                        ['Content-Type' => $request->headers->get('Content-Type')],
                        $request->getContent()
                    )
                )
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }

        return new Values\CreatedObjectState(
            [
                'objectState' => new RestObjectState(
                    $createdObjectState,
                    $objectStateGroup->id
                ),
            ]
        );
    }

    /**
     * Loads an object state group.
     *
     * @param $objectStateGroupId
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup
     */
    public function loadObjectStateGroup($objectStateGroupId)
    {
        return $this->objectStateService->loadObjectStateGroup($objectStateGroupId, Language::ALL);
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

    /**
     * Returns a list of all object state groups.
     *
     * @return \Ibexa\Rest\Server\Values\ObjectStateGroupList
     */
    public function loadObjectStateGroups()
    {
        return new Values\ObjectStateGroupList(
            $this->objectStateService->loadObjectStateGroups(0, -1, Language::ALL)
        );
    }

    /**
     * Returns a list of all object states of the given group.
     *
     * @param $objectStateGroupId
     *
     * @return \Ibexa\Rest\Server\Values\ObjectStateList
     */
    public function loadObjectStates($objectStateGroupId)
    {
        $objectStateGroup = $this->objectStateService->loadObjectStateGroup($objectStateGroupId);

        return new Values\ObjectStateList(
            $this->objectStateService->loadObjectStates($objectStateGroup, Language::ALL),
            $objectStateGroup->id
        );
    }

    /**
     * The given object state group including the object states is deleted.
     *
     * @param $objectStateGroupId
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function deleteObjectStateGroup($objectStateGroupId)
    {
        $this->objectStateService->deleteObjectStateGroup(
            $this->objectStateService->loadObjectStateGroup($objectStateGroupId)
        );

        return new Values\NoContent();
    }

    /**
     * The given object state is deleted.
     *
     * @param $objectStateId
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function deleteObjectState($objectStateId)
    {
        $this->objectStateService->deleteObjectState(
            $this->objectStateService->loadObjectState($objectStateId)
        );

        return new Values\NoContent();
    }

    /**
     * Updates an object state group.
     *
     * @param $objectStateGroupId
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup
     */
    public function updateObjectStateGroup($objectStateGroupId, Request $request)
    {
        $updateStruct = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        $objectStateGroup = $this->objectStateService->loadObjectStateGroup($objectStateGroupId);

        try {
            $updatedStateGroup = $this->objectStateService->updateObjectStateGroup($objectStateGroup, $updateStruct);

            return $updatedStateGroup;
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }
    }

    /**
     * Updates an object state.
     *
     * @param $objectStateGroupId
     * @param $objectStateId
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     *
     * @return \Ibexa\Rest\Values\RestObjectState
     */
    public function updateObjectState($objectStateGroupId, $objectStateId, Request $request)
    {
        $updateStruct = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        $objectState = $this->objectStateService->loadObjectState($objectStateId);

        try {
            $updatedObjectState = $this->objectStateService->updateObjectState($objectState, $updateStruct);

            return new RestObjectState($updatedObjectState, $objectStateGroupId);
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }
    }

    /**
     * Returns the object states of content.
     *
     * @param $contentId
     *
     * @return \Ibexa\Rest\Values\ContentObjectStates
     */
    public function getObjectStatesForContent($contentId)
    {
        $groups = $this->objectStateService->loadObjectStateGroups();
        $contentInfo = $this->contentService->loadContentInfo($contentId);

        $contentObjectStates = [];

        foreach ($groups as $group) {
            try {
                $state = $this->objectStateService->getContentState($contentInfo, $group);
                $contentObjectStates[] = new RestObjectState($state, $group->id);
            } catch (NotFoundException $e) {
                // Do nothing
            }
        }

        return new ContentObjectStates($contentObjectStates);
    }

    /**
     * Updates object states of content
     * An object state in the input overrides the state of the object state group.
     *
     * @param $contentId
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     *
     * @return \Ibexa\Rest\Values\ContentObjectStates
     */
    public function setObjectStatesForContent($contentId, Request $request)
    {
        $newObjectStates = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        $countByGroups = [];
        foreach ($newObjectStates as $newObjectState) {
            $groupId = (int)$newObjectState->groupId;
            if (array_key_exists($groupId, $countByGroups)) {
                ++$countByGroups[$groupId];
            } else {
                $countByGroups[$groupId] = 1;
            }
        }

        foreach ($countByGroups as $groupId => $count) {
            if ($count > 1) {
                throw new ForbiddenException(
                    /** @Ignore */
                    "Multiple Object states provided for group with ID $groupId"
                );
            }
        }

        $contentInfo = $this->contentService->loadContentInfo($contentId);

        $contentObjectStates = [];
        foreach ($newObjectStates as $newObjectState) {
            $objectStateGroup = $this->objectStateService->loadObjectStateGroup($newObjectState->groupId);
            $this->objectStateService->setContentState($contentInfo, $objectStateGroup, $newObjectState->objectState);
            $contentObjectStates[(int)$objectStateGroup->id] = $newObjectState;
        }

        return new ContentObjectStates($contentObjectStates);
    }
}
