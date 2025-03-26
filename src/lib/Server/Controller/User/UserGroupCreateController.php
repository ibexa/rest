<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Controller\User;

use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Values;
use LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Post(
    uriTemplate: '/user/groups/subgroups',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Create a top level User Group',
        description: 'Creates a top level User Group under the root. To create a child group under a parent group use \'/user/groups/{path}/subgroups\'.',
        tags: [
            'User Group',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the new User Group is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The UserGroupCreate schema encoded in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.UserGroupCreate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/UserGroupCreate',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/groups/path/subgroups/POST/UserGroupCreate.xml.example',
                ],
                'application/vnd.ibexa.api.UserGroupCreate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/UserGroupCreateWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/groups/path/subgroups/POST/UserGroupCreate.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_CREATED => [
                'description' => 'Created - the User Group has been created',
                'content' => [
                    'application/vnd.ibexa.api.UserGroup+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserGroup',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/groups/path/subgroups/POST/UserGroup.xml.example',
                    ],
                    'application/vnd.ibexa.api.UserGroup+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserGroupWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/groups/path/subgroups/POST/UserGroup.json.example',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - the input does not match the input schema definition.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to create this User Group.',
            ],
        ],
    ),
)]
#[Post(
    uriTemplate: '/user/groups/{path}/subgroups',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Create User Group',
        description: 'Creates a new User Group under the given parent. To create a top level group use \'/user/groups/subgroups\'.',
        tags: [
            'User Group',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the new User Group is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The UserGroupCreate schema encoded in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'path',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.UserGroupCreate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/UserGroupCreate',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/groups/path/subgroups/POST/UserGroupCreate.xml.example',
                ],
                'application/vnd.ibexa.api.UserGroupCreate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/UserGroupCreateWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/groups/path/subgroups/POST/UserGroupCreate.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_CREATED => [
                'content' => [
                    'application/vnd.ibexa.api.UserGroup+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserGroup',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/groups/path/subgroups/POST/UserGroup.xml.example',
                    ],
                    'application/vnd.ibexa.api.UserGroup+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserGroupWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/groups/path/subgroups/POST/UserGroup.json.example',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - the input does not match the input schema definition.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to create this User Group.',
            ],
        ],
    ),
)]
final class UserGroupCreateController extends UserBaseController
{
    /**
     * Create a new user group under the given parent
     * To create a top level group use /user/groups/1/5/subgroups.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     */
    public function createUserGroup(string $groupPath, Request $request): Values\CreatedUserGroup
    {
        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($groupPath)
        );

        $createdUserGroup = $this->userService->createUserGroup(
            $this->inputDispatcher->parse(
                new Message(
                    ['Content-Type' => $request->headers->get('Content-Type')],
                    $request->getContent()
                )
            ),
            $this->userService->loadUserGroup(
                $userGroupLocation->contentId
            )
        );

        $createdContentInfo = $createdUserGroup->getVersionInfo()->getContentInfo();

        if ($createdContentInfo->mainLocationId === null) {
            throw new LogicException();
        }

        $createdLocation = $this->locationService->loadLocation($createdContentInfo->mainLocationId);
        $contentType = $this->contentTypeService->loadContentType($createdContentInfo->contentTypeId);

        return new Values\CreatedUserGroup(
            [
                'userGroup' => new Values\RestUserGroup(
                    $createdUserGroup,
                    $contentType,
                    $createdContentInfo,
                    $createdLocation,
                    iterator_to_array($this->relationListFacade->getRelations($createdUserGroup->getVersionInfo())),
                ),
            ]
        );
    }
}
