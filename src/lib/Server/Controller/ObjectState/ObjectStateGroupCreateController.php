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
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Post(
    uriTemplate: '/content/objectstategroups',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Create Object state group',
        description: 'Creates a new Object state group.',
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
class ObjectStateGroupCreateController extends RestController
{
    protected ObjectStateService $objectStateService;

    protected ContentService $contentService;

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
}
