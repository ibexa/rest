<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\ObjectState;

use ApiPlatform\Metadata\Patch;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Patch(
    uriTemplate: '/content/objectstategroups/{objectStateGroupId}',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Update Object state group',
        description: 'Updates an Object state group. PATCH or POST with header X-HTTP-Method-Override PATCH.',
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
class ObjectStateGroupUpdateController extends RestController
{
    protected ObjectStateService $objectStateService;

    protected ContentService $contentService;

    public function __construct(ObjectStateService $objectStateService, ContentService $contentService)
    {
        $this->objectStateService = $objectStateService;
        $this->contentService = $contentService;
    }

    /**
     * Updates an object state group.
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     */
    public function updateObjectStateGroup(int $objectStateGroupId, Request $request): \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup
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
}
