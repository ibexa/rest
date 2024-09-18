<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\ObjectState;

use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values;
use Ibexa\Rest\Values\RestObjectState;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
class ObjectStateCreateController extends RestController
{
    protected ObjectStateService $objectStateService;

    protected ContentService $contentService;

    public function __construct(ObjectStateService $objectStateService, ContentService $contentService)
    {
        $this->objectStateService = $objectStateService;
        $this->contentService = $contentService;
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
}
