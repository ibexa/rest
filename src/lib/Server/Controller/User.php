<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Controller;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions as ApiExceptions;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\SectionService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\User\User as RepositoryUser;
use Ibexa\Contracts\Core\Repository\Values\User\UserGroupRoleAssignment;
use Ibexa\Contracts\Core\Repository\Values\User\UserRoleAssignment;
use Ibexa\Contracts\Rest\Exceptions\NotFoundException;
use Ibexa\Core\Base\Exceptions\UnauthorizedException;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values;
use Ibexa\Rest\Value as RestValue;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

#[Get(
    uriTemplate: '/user/users',
    name: 'List Users',
    openapi: new Model\Operation(
        summary: 'Load Users either for a given remote ID or Role.',
        tags: [
            'User',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'UserList - If set, the User list is returned in XML or JSON format. UserRefList - If set, the link list of Users is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - Loads Users either for a given remote ID or Role.',
                'content' => [
                    'application/vnd.ibexa.api.UserList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/GET/User.xml.example',
                    ],
                    'application/vnd.ibexa.api.UserList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/GET/User.json.example',
                    ],
                    'application/vnd.ibexa.api.UserRefList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserRefList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/GET/UserRefList.xml.example',
                    ],
                    'application/vnd.ibexa.api.UserRefList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserRefListWrapper',
                        ],
                    ],
                ],
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'If there are no visibile Users matching the filter.',
            ],
        ],
    ),
)]
#[Head(
    uriTemplate: '/user/users',
    name: 'Verify Users',
    openapi: new Model\Operation(
        summary: 'Verifies if there are Users matching given filter.',
        tags: [
            'User',
        ],
        parameters: [
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - verifies if there are Users matching the given filter.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - there are no visibile Users matching the filter.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/user/users/current',
    name: 'Load current User',
    openapi: new Model\Operation(
        summary: 'Redirects to current User, if available.',
        tags: [
            'User',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the User is returned in XML or JSON format (after redirection).',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_TEMPORARY_REDIRECT => [
                'description' => 'OK.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'User is not currently logged in.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/user/users/{userId}',
    name: 'Load User',
    openapi: new Model\Operation(
        summary: 'Loads User with the given ID.',
        tags: [
            'User',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the User is returned in XML or JSON format.',
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
                name: 'userId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - the User with the given ID.',
                'content' => [
                    'application/vnd.ibexa.api.User+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/GET/User.xml.example',
                    ],
                    'application/vnd.ibexa.api.User+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/GET/User.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user has no permission to read Users.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the User does not exist.',
            ],
        ],
    ),
)]
#[Patch(
    uriTemplate: '/user/users/{userId}',
    name: 'Update User',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Updates a User.',
        tags: [
            'User',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the updated User is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The UserUpdate schema encoded in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'If-Match',
                in: 'header',
                required: true,
                description: 'Performs a PATCH only if the specified ETag is the current one.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'userId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.UserUpdate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/UserUpdate',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/PATCH/UserUpdate.xml.example',
                ],
                'application/vnd.ibexa.api.UserUpdate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/UserUpdateWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/PATCH/UserUpdate.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - User updated.',
                'content' => [
                    'application/vnd.ibexa.api.User+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/User',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/PATCH/User.xml.example',
                    ],
                    'application/vnd.ibexa.api.User+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/PATCH/User.json.example',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - the input does not match the input schema definition.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to update the User.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the User does not exist.',
            ],
            Response::HTTP_PRECONDITION_FAILED => [
                'description' => 'Error - the current ETag does not match with the provided one in the If-Match header.',
            ],
        ],
    ),
)]
#[Delete(
    uriTemplate: '/user/users/{userId}',
    name: 'Delete User',
    openapi: new Model\Operation(
        summary: 'Deletes the given User.',
        tags: [
            'User',
        ],
        parameters: [
            new Model\Parameter(
                name: 'userId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'No Content.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to delete this User.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - the user is the same as the authenticated User.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the User does not exist.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/user/users/{userId}/groups',
    name: 'Load Groups of User',
    openapi: new Model\Operation(
        summary: 'Returns a list of User Groups the User belongs to. The returned list includes the resources for unassigning a User Group if the User is in multiple groups.',
        tags: [
            'User',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the link list of User Groups is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'userId',
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
                    'application/vnd.ibexa.api.UserGroupRefList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserGroupRefList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/groups/POST/UserGroupRefList.xml.example',
                    ],
                    'application/vnd.ibexa.api.UserGroupRefList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserGroupRefListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/groups/group_id/UserGroupRefList.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user has no permission to read User Groups.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the user does not exist.',
            ],
        ],
    ),
)]
#[Post(
    uriTemplate: '/user/users/{userId}/groups',
    name: 'Assign User Group',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapiContext: ['requestBody' => false],
    openapi: new Model\Operation(
        summary: 'Assigns the User to a User Group.',
        tags: [
            'User',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the link list of User Groups is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'userId',
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
                    'application/vnd.ibexa.api.UserGroupRefList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserGroupRefList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/groups/POST/UserGroupRefList.xml.example',
                    ],
                    'application/vnd.ibexa.api.UserGroupRefList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserGroupRefListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/groups/group_id/UserGroupRefList.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to assign User Groups.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - the new User Group does not exist or the User is already in this group.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the User does not exist.',
            ],
        ],
    ),
)]
#[Delete(
    uriTemplate: '/user/users/{userId}/groups/{groupId}',
    name: 'Unassign User Group',
    openapi: new Model\Operation(
        summary: 'Unassigns the User from a User Group.',
        tags: [
            'User',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the link list of User Groups is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'userId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'groupId',
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
                    'application/vnd.ibexa.api.UserGroupRefList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserGroupRefList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/groups/POST/UserGroupRefList.xml.example',
                    ],
                    'application/vnd.ibexa.api.UserGroupRefList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserGroupRefListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/groups/group_id/UserGroupRefList.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to unassign User Groups.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - the User is not in the given group.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the User does not exist.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/user/users/{userId}/roles',
    name: 'Load Roles for User',
    openapi: new Model\Operation(
        summary: 'Returns a list of all Roles assigned to the given User.',
        tags: [
            'User',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the Role assignment list is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'userId',
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
                    'application/vnd.ibexa.api.RoleAssignmentList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RoleAssignmentList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/roles/POST/RoleAssignmentList.xml.example',
                    ],
                    'application/vnd.ibexa.api.RoleAssignmentList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RoleAssignmentListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/roles/role_id/DELETE/RoleAssignmentList.json.example',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - the user has no permission to read Roles.',
            ],
        ],
    ),
)]
#[Post(
    uriTemplate: '/user/users/{userId}/roles',
    name: 'Assign Role to User',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Assigns a Role to a user.',
        tags: [
            'User',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the updated Role assignment list is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The RoleAssignInput schema encoded in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'userId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.RoleAssignInput+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/RoleAssignInput',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/roles/POST/RoleAssignInput.xml.example',
                ],
                'application/vnd.ibexa.api.RoleAssignInput+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/RoleAssignInputWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/roles/POST/RoleAssignInput.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_OK => [
                'content' => [
                    'application/vnd.ibexa.api.RoleAssignmentList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RoleAssignmentList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/roles/POST/RoleAssignmentList.xml.example',
                    ],
                    'application/vnd.ibexa.api.RoleAssignmentList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RoleAssignmentListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/roles/role_id/DELETE/RoleAssignmentList.json.example',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - validation of limitation in RoleAssignInput fails.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to assign this Role.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/user/users/{userId}/roles/{roleId}',
    name: 'Load User Role Assignment',
    openapi: new Model\Operation(
        summary: 'Returns a Role assignment to the given User.',
        tags: [
            'User',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the Role assignment list is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'userId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'roleId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - Role assignment to the given User Group.',
                'content' => [
                    'application/vnd.ibexa.api.RoleAssignment+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RoleAssignment',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/roles/role_id/GET/RoleAssignment.xml.example',
                    ],
                    'application/vnd.ibexa.api.RoleAssignment+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RoleAssignmentWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/roles/role_id/GET/RoleAssignment.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user has no permission to read Roles.',
            ],
        ],
    ),
)]
#[Delete(
    uriTemplate: '/user/users/{userId}/roles/{roleId}',
    name: 'Unassign Role from User',
    openapi: new Model\Operation(
        summary: 'The given Role is removed from the user.',
        tags: [
            'User',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the updated Role assignment list is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'userId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'roleId',
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
                    'application/vnd.ibexa.api.RoleAssignmentList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RoleAssignmentList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/roles/POST/RoleAssignmentList.xml.example',
                    ],
                    'application/vnd.ibexa.api.RoleAssignmentList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RoleAssignmentListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/roles/role_id/DELETE/RoleAssignmentList.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to delete this content type.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/user/users/{userId}/drafts',
    name: 'Load user drafts',
    openapi: new Model\Operation(
        summary: 'Loads user\'s drafts',
        tags: [
            '',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the version list is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'userId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - List the draft versions',
                'content' => [
                    'application/vnd.ibexa.api.VersionList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/VersionList',
                        ],
                    ],
                    'application/vnd.ibexa.api.VersionList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/VersionListWrapper',
                        ],
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the current user is not authorized to list the drafts of the given user.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/user/groups',
    name: 'Load User Groups',
    openapi: new Model\Operation(
        summary: 'Loads User Groups for either an an ID or a remote ID or a Role.',
        tags: [
            'User Group',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'UserGroupList - If set, the User Group List is returned in XML or JSON format. UserGroupRefList - If set, the link list of User Group is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'content' => [
                    'application/vnd.ibexa.api.UserGroupList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserGroupList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/groups/GET/UserGroupList.xml.example',
                    ],
                    'application/vnd.ibexa.api.UserGroupList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserGroupListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/groups/GET/UserGroupList.json.example',
                    ],
                    'application/vnd.ibexa.api.UserGroupRefList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserGroupRefList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/groups/POST/UserGroupRefList.xml.example',
                    ],
                    'application/vnd.ibexa.api.UserGroupRefList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserGroupRefListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/groups/group_id/UserGroupRefList.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user has no permission to read User Groups.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/user/groups/root',
    name: 'Get root User Group',
    openapi: new Model\Operation(
        summary: 'Redirects to the root User Group.',
        tags: [
            'User Group',
        ],
        parameters: [
        ],
        responses: [
            Response::HTTP_MOVED_PERMANENTLY => [
                'description' => 'Moved permanently.',
            ],
        ],
    ),
)]
#[Post(
    uriTemplate: '/user/groups/subgroups',
    name: 'Create a top level User Group',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Creates a top level User Group under the root. To create a child group under a parent group use \'/user/groups/{path}/subgroups\'.',
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
#[Get(
    uriTemplate: '/user/groups/{path}',
    name: 'Load User Group',
    openapi: new Model\Operation(
        summary: 'Loads User Groups for the given {path}.',
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
                name: 'If-None-Match',
                in: 'header',
                required: true,
                description: 'ETag',
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
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - loads User Groups.',
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
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user has no permission to read User Groups.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the User Group does not exist.',
            ],
        ],
    ),
)]
#[Patch(
    uriTemplate: '/user/groups/{path}',
    name: 'Update User Group',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Updates a User Group. PATCH or POST with header X-HTTP-Method-Override PATCH.',
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
                description: 'The UserGroupUpdate schema encoded in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'If-Match',
                in: 'header',
                required: true,
                description: 'Performs the PATCH only if the specified ETag is the current one. Otherwise a 412 is returned.',
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
                'application/vnd.ibexa.api.UserGroupUpdate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/UserGroupUpdate',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/groups/path/PATCH/UserGroupUpdate.xml.example',
                ],
                'application/vnd.ibexa.api.UserGroupUpdate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/UserGroupUpdateWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/groups/path/PATCH/UserGroupUpdate.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - updated User Group.',
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
                'description' => 'Error - the user is not authorized to update the User Group.',
            ],
            Response::HTTP_PRECONDITION_FAILED => [
                'description' => 'Error -	if the current ETag does not match with the one provided in the If-Match header.',
            ],
        ],
    ),
)]
#[Delete(
    uriTemplate: '/user/groups/{path}',
    name: 'Delete User Group',
    openapi: new Model\Operation(
        summary: 'The given User Group is deleted.',
        tags: [
            'User Group',
        ],
        parameters: [
            new Model\Parameter(
                name: 'path',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'No content - the given User Group is deleted.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to delete this content type.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - the User Group is not empty.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/user/groups/{path}/users',
    name: 'Load Users of Group',
    openapi: new Model\Operation(
        summary: 'Loads the Users of the Group with the given ID.',
        tags: [
            'User Group',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'UserList - If set, the User list returned in XML or JSON format. UserRefList - If set, the link list of Users returned in XML or JSON format.',
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
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - the Users of the Group with the given ID.',
                'content' => [
                    'application/vnd.ibexa.api.UserList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserList',
                        ],
                    ],
                    'application/vnd.ibexa.api.UserList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserList',
                        ],
                    ],
                    'application/vnd.ibexa.api.UserRefList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserRefList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/GET/UserRefList.xml.example',
                    ],
                    'application/vnd.ibexa.api.UserRefList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserRefListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/groups/id/users/GET/UserRefList.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user has no permission to read User Groups.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the User Group does not exist.',
            ],
        ],
    ),
)]
#[Post(
    uriTemplate: '/user/groups/{path}/users',
    name: 'Create User',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Creates a new User in the given Group.',
        tags: [
            'User Group',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the new User is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The UserCreate schema encoded in XML or JSON format.',
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
                'application/vnd.ibexa.api.UserCreate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/UserCreate',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/groups/path/users/POST/UserCreate.xml.example',
                ],
                'application/vnd.ibexa.api.UserCreate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/UserCreateWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/groups/path/users/POST/UserCreate.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_CREATED => [
                'content' => [
                    'application/vnd.ibexa.api.User+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/User',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/PATCH/User.xml.example',
                    ],
                    'application/vnd.ibexa.api.User+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/PATCH/User.json.example',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - the input does not match the input schema definition.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to create this User.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - a User with the same login already exists.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the Group with the given ID does not exist.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/user/groups/{path}/subgroups',
    name: 'Load subgroups',
    openapi: new Model\Operation(
        summary: 'Returns a list of the subgroups.',
        tags: [
            'User Group',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'UserGroupList - If set, the User Group list is returned in XML or JSON format. UserGroupRefList - If set, the link list of User Groups is returned in XML or JSON format.',
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
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - list of the subgroups.',
                'content' => [
                    'application/vnd.ibexa.api.UserGroupList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserGroupList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/groups/GET/UserGroupList.xml.example',
                    ],
                    'application/vnd.ibexa.api.UserGroupList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserGroupListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/groups/GET/UserGroupList.json.example',
                    ],
                    'application/vnd.ibexa.api.UserGroupRefList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserGroupRefList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/groups/POST/UserGroupRefList.xml.example',
                    ],
                    'application/vnd.ibexa.api.UserGroupRefList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserGroupRefListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/groups/group_id/UserGroupRefList.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user has no permission to read User Groups.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the User Group does not exist.',
            ],
        ],
    ),
)]
#[Post(
    uriTemplate: '/user/groups/{path}/subgroups',
    name: 'Create User Group',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Creates a new User Group under the given parent. To create a top level group use \'/user/groups/subgroups\'.',
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
#[Get(
    uriTemplate: '/user/policies',
    name: 'List Policies for User',
    openapi: new Model\Operation(
        summary: 'Search all Policies which are applied to a given User.',
        tags: [
            '',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the Policy list is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - Policies which are applied to a given User.',
                'content' => [
                    'application/vnd.ibexa.api.PolicyList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/PolicyList',
                        ],
                        'example' => self::POLICY_LIST_XML_EXAMPLE,
                    ],
                    'application/vnd.ibexa.api.PolicyList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/PolicyListWrapper',
                        ],
                        'example' => self::POLICY_LIST_JSON_EXAMPLE,
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user has no permission to read Roles.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/user/current',
    name: 'Load current User',
    openapi: new Model\Operation(
        summary: 'Loads the current user.',
        tags: [
            'User Current',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the User is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - the User with the given ID.',
                'content' => [
                    'application/vnd.ibexa.api.User+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/GET/User.xml.example',
                    ],
                    'application/vnd.ibexa.api.User+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/GET/User.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user has no permission to read Users. For example, Anonymous user can\'t load oneself.',
            ],
        ],
    ),
)]
/**
 * User controller.
 */
final class User extends RestController
{
    protected UserService $userService;

    protected RoleService $roleService;

    protected ContentService $contentService;

    protected ContentTypeService $contentTypeService;

    protected LocationService $locationService;

    protected SectionService $sectionService;

    /**
     * Repository.
     *
     * @var \Ibexa\Contracts\Core\Repository\Repository
     */
    protected $repository;

    private PermissionResolver $permissionResolver;

    public function __construct(
        UserService $userService,
        RoleService $roleService,
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        LocationService $locationService,
        SectionService $sectionService,
        Repository $repository,
        PermissionResolver $permissionResolver
    ) {
        $this->userService = $userService;
        $this->roleService = $roleService;
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->locationService = $locationService;
        $this->sectionService = $sectionService;
        $this->repository = $repository;
        $this->permissionResolver = $permissionResolver;
    }

    /**
     * Redirects to the root user group.
     */
    public function loadRootUserGroup(): Values\PermanentRedirect
    {
        //@todo Replace hardcoded value with one loaded from settings
        return new Values\PermanentRedirect(
            $this->router->generate('ibexa.rest.load_user_group', ['groupPath' => '/1/5'])
        );
    }

    /**
     * Loads a user group for the given path.
     */
    public function loadUserGroup(string $groupPath): RestValue
    {
        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($groupPath)
        );

        if (trim($userGroupLocation->pathString, '/') !== $groupPath) {
            throw new NotFoundException(
                "Could not find a Location with path string $groupPath"
            );
        }

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId,
            Language::ALL
        );
        $userGroupContentInfo = $userGroup->getVersionInfo()->getContentInfo();
        $contentType = $this->contentTypeService->loadContentType($userGroupContentInfo->contentTypeId);

        return new Values\CachedValue(
            new Values\RestUserGroup(
                $userGroup,
                $contentType,
                $userGroupContentInfo,
                $userGroupLocation,
                $this->contentService->loadRelations($userGroup->getVersionInfo())
            ),
            ['locationId' => $userGroupLocation->id]
        );
    }

    public function loadUser(int $userId): RestValue
    {
        $user = $this->userService->loadUser($userId, Language::ALL);

        $userContentInfo = $user->getVersionInfo()->getContentInfo();
        $contentType = $this->contentTypeService->loadContentType($userContentInfo->contentTypeId);

        try {
            $userMainLocation = $this->locationService->loadLocation($userContentInfo->mainLocationId);
            $relations = $this->contentService->loadRelations($user->getVersionInfo());
        } catch (UnauthorizedException $e) {
            // TODO: Hack for special case to allow current logged in user to load him/here self (but not relations)
            if ($user->id == $this->permissionResolver->getCurrentUserReference()->getUserId()) {
                $userMainLocation = $this->repository->sudo(
                    function () use ($userContentInfo) {
                        return $this->locationService->loadLocation($userContentInfo->mainLocationId);
                    }
                );
                // user may not have permissions to read related content, for security reasons do not use sudo().
                $relations = [];
            } else {
                throw $e;
            }
        }

        return new Values\CachedValue(
            new Values\RestUser(
                $user,
                $contentType,
                $userContentInfo,
                $userMainLocation,
                $relations
            ),
            ['locationId' => $userContentInfo->mainLocationId]
        );
    }

    /**
     * @see \Symfony\Component\Security\Http\Controller\UserValueResolver
     */
    public function redirectToCurrentUser(?UserInterface $user): Values\TemporaryRedirect
    {
        if ($user === null) {
            throw new UnauthorizedHttpException('', 'Not logged in.');
        }

        $userReference = $this->permissionResolver->getCurrentUserReference();

        return new Values\TemporaryRedirect(
            $this->router->generate('ibexa.rest.load_user', ['userId' => $userReference->getUserId()])
        );
    }

    /**
     * Create a new user group under the given parent
     * To create a top level group use /user/groups/1/5/subgroups.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     * @throws \Ibexa\Core\Base\Exceptions\UnauthorizedException
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
        $createdLocation = $this->locationService->loadLocation($createdContentInfo->mainLocationId);
        $contentType = $this->contentTypeService->loadContentType($createdContentInfo->contentTypeId);

        return new Values\CreatedUserGroup(
            [
                'userGroup' => new Values\RestUserGroup(
                    $createdUserGroup,
                    $contentType,
                    $createdContentInfo,
                    $createdLocation,
                    $this->contentService->loadRelations($createdUserGroup->getVersionInfo())
                ),
            ]
        );
    }

    /**
     * Create a new user group in the given group.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     * @throws \Ibexa\Core\Base\Exceptions\UnauthorizedException
     */
    public function createUser(string $groupPath, Request $request): Values\CreatedUser
    {
        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($groupPath)
        );
        $userGroup = $this->userService->loadUserGroup($userGroupLocation->contentId);

        $userCreateStruct = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        try {
            $createdUser = $this->userService->createUser($userCreateStruct, [$userGroup]);
        } catch (ApiExceptions\InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }

        $createdContentInfo = $createdUser->getVersionInfo()->getContentInfo();
        $createdLocation = $this->locationService->loadLocation($createdContentInfo->mainLocationId);
        $contentType = $this->contentTypeService->loadContentType($createdContentInfo->contentTypeId);

        return new Values\CreatedUser(
            [
                'user' => new Values\RestUser(
                    $createdUser,
                    $contentType,
                    $createdContentInfo,
                    $createdLocation,
                    $this->contentService->loadRelations($createdUser->getVersionInfo())
                ),
            ]
        );
    }

    public function updateUserGroup(string $groupPath, Request $request): Values\RestUserGroup
    {
        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($groupPath)
        );

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId
        );

        $updateStruct = $this->inputDispatcher->parse(
            new Message(
                [
                    'Content-Type' => $request->headers->get('Content-Type'),
                    // @todo Needs refactoring! Temporary solution so parser has access to URL
                    'Url' => $request->getPathInfo(),
                ],
                $request->getContent()
            )
        );

        if ($updateStruct->sectionId !== null) {
            $section = $this->sectionService->loadSection($updateStruct->sectionId);
            $this->sectionService->assignSection(
                $userGroup->getVersionInfo()->getContentInfo(),
                $section
            );
        }

        $updatedGroup = $this->userService->updateUserGroup($userGroup, $updateStruct->userGroupUpdateStruct);
        $contentType = $this->contentTypeService->loadContentType(
            $updatedGroup->getVersionInfo()->getContentInfo()->contentTypeId
        );

        return new Values\RestUserGroup(
            $updatedGroup,
            $contentType,
            $updatedGroup->getVersionInfo()->getContentInfo(),
            $userGroupLocation,
            $this->contentService->loadRelations($updatedGroup->getVersionInfo())
        );
    }

    public function updateUser(int $userId, Request $request): Values\RestUser
    {
        $user = $this->userService->loadUser($userId);

        $updateStruct = $this->inputDispatcher->parse(
            new Message(
                [
                    'Content-Type' => $request->headers->get('Content-Type'),
                    // @todo Needs refactoring! Temporary solution so parser has access to URL
                    'Url' => $request->getPathInfo(),
                ],
                $request->getContent()
            )
        );

        if ($updateStruct->sectionId !== null) {
            $section = $this->sectionService->loadSection($updateStruct->sectionId);
            $this->sectionService->assignSection(
                $user->getVersionInfo()->getContentInfo(),
                $section
            );
        }

        $updatedUser = $this->userService->updateUser($user, $updateStruct->userUpdateStruct);
        $updatedContentInfo = $updatedUser->getVersionInfo()->getContentInfo();
        $mainLocation = $this->locationService->loadLocation($updatedContentInfo->mainLocationId);
        $contentType = $this->contentTypeService->loadContentType($updatedContentInfo->contentTypeId);

        return new Values\RestUser(
            $updatedUser,
            $contentType,
            $updatedContentInfo,
            $mainLocation,
            $this->contentService->loadRelations($updatedUser->getVersionInfo())
        );
    }

    /**
     * Given user group is deleted.
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     * @throws \Ibexa\Core\Base\Exceptions\UnauthorizedException
     */
    public function deleteUserGroup(string $groupPath): Values\NoContent
    {
        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($groupPath)
        );

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId
        );

        // Load one user to see if user group is empty or not
        $users = $this->userService->loadUsersOfUserGroup($userGroup, 0, 1);
        if (!empty($users)) {
            throw new Exceptions\ForbiddenException('Cannot delete non-empty User Groups');
        }

        $this->userService->deleteUserGroup($userGroup);

        return new Values\NoContent();
    }

    /**
     * Given user is deleted.
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     * @throws \Ibexa\Core\Base\Exceptions\UnauthorizedException
     */
    public function deleteUser(int $userId): Values\NoContent
    {
        $user = $this->userService->loadUser($userId);

        if ($user->id == $this->permissionResolver->getCurrentUserReference()->getUserId()) {
            throw new Exceptions\ForbiddenException('Cannot delete the currently authenticated User');
        }

        $this->userService->deleteUser($user);

        return new Values\NoContent();
    }

    /**
     * Loads users.
     */
    public function loadUsers(Request $request): RestValue
    {
        $restUsers = [];

        try {
            if ($request->query->has('roleId')) {
                $restUsers = $this->loadUsersAssignedToRole(
                    $this->requestParser->parseHref($request->query->get('roleId'), 'roleId')
                );
            } elseif ($request->query->has('remoteId')) {
                $restUsers = [
                    $this->buildRestUserObject(
                        $this->userService->loadUser(
                            $this->contentService->loadContentInfoByRemoteId($request->query->get('remoteId'))->id,
                            Language::ALL
                        )
                    ),
                ];
            } elseif ($request->query->has('login')) {
                $restUsers = [
                    $this->buildRestUserObject(
                        $this->userService->loadUserByLogin($request->query->get('login'), Language::ALL)
                    ),
                ];
            } elseif ($request->query->has('email')) {
                foreach ($this->userService->loadUsersByEmail($request->query->get('email'), Language::ALL) as $user) {
                    $restUsers[] = $this->buildRestUserObject($user);
                }
            }
        } catch (ApiExceptions\UnauthorizedException $e) {
            $restUsers = [];
        }

        if (empty($restUsers)) {
            throw new NotFoundException('Could not find Users with the given filter');
        }

        if ($this->getMediaType($request) === 'application/vnd.ibexa.api.userlist') {
            return new Values\UserList($restUsers, $request->getPathInfo());
        }

        return new Values\UserRefList($restUsers, $request->getPathInfo());
    }

    public function verifyUsers(Request $request): Values\OK
    {
        // We let the NotFoundException loadUsers throws if there are no results pass.
        $this->loadUsers($request)->users;

        return new Values\OK();
    }

    /**
     * Loads a list of users assigned to role.
     *
     * @param mixed $roleId
     *
     * @return \Ibexa\Rest\Server\Values\RestUser[]
     */
    public function loadUsersAssignedToRole($roleId): array
    {
        $role = $this->roleService->loadRole($roleId);
        $roleAssignments = $this->roleService->getRoleAssignments($role);

        $restUsers = [];

        foreach ($roleAssignments as $roleAssignment) {
            if ($roleAssignment instanceof UserRoleAssignment) {
                $restUsers[] = $this->buildRestUserObject($roleAssignment->getUser());
            }
        }

        return $restUsers;
    }

    private function buildRestUserObject(RepositoryUser $user): Values\RestUser
    {
        return new Values\RestUser(
            $user,
            $this->contentTypeService->loadContentType($user->contentInfo->contentTypeId),
            $user->contentInfo,
            $this->locationService->loadLocation($user->contentInfo->mainLocationId),
            $this->contentService->loadRelations($user->getVersionInfo())
        );
    }

    /**
     * Loads user groups.
     */
    public function loadUserGroups(Request $request): RestValue
    {
        $restUserGroups = [];
        if ($request->query->has('id')) {
            $userGroup = $this->userService->loadUserGroup($request->query->get('id'), Language::ALL);
            $userGroupContentInfo = $userGroup->getVersionInfo()->getContentInfo();
            $userGroupMainLocation = $this->locationService->loadLocation($userGroupContentInfo->mainLocationId);
            $contentType = $this->contentTypeService->loadContentType($userGroupContentInfo->contentTypeId);

            $restUserGroups = [
                new Values\RestUserGroup(
                    $userGroup,
                    $contentType,
                    $userGroupContentInfo,
                    $userGroupMainLocation,
                    $this->contentService->loadRelations($userGroup->getVersionInfo())
                ),
            ];
        } elseif ($request->query->has('roleId')) {
            $restUserGroups = $this->loadUserGroupsAssignedToRole($request->query->get('roleId'));
        } elseif ($request->query->has('remoteId')) {
            $restUserGroups = [
                $this->loadUserGroupByRemoteId($request),
            ];
        }

        if ($this->getMediaType($request) === 'application/vnd.ibexa.api.usergrouplist') {
            return new Values\UserGroupList($restUserGroups, $request->getPathInfo());
        }

        return new Values\UserGroupRefList($restUserGroups, $request->getPathInfo());
    }

    /**
     * Loads a user group by its remote ID.
     */
    public function loadUserGroupByRemoteId(Request $request): Values\RestUserGroup
    {
        $contentInfo = $this->contentService->loadContentInfoByRemoteId($request->query->get('remoteId'));
        $userGroup = $this->userService->loadUserGroup($contentInfo->id, Language::ALL);
        $userGroupLocation = $this->locationService->loadLocation($contentInfo->mainLocationId);
        $contentType = $this->contentTypeService->loadContentType($contentInfo->contentTypeId);

        return new Values\RestUserGroup(
            $userGroup,
            $contentType,
            $contentInfo,
            $userGroupLocation,
            $this->contentService->loadRelations($userGroup->getVersionInfo())
        );
    }

    /**
     * Loads a list of user groups assigned to role.
     *
     * @param mixed $roleId
     *
     * @return \Ibexa\Rest\Server\Values\RestUserGroup[]
     */
    public function loadUserGroupsAssignedToRole($roleId): array
    {
        $role = $this->roleService->loadRole($roleId);
        $roleAssignments = $this->roleService->getRoleAssignments($role);

        $restUserGroups = [];

        foreach ($roleAssignments as $roleAssignment) {
            if ($roleAssignment instanceof UserGroupRoleAssignment) {
                $userGroup = $roleAssignment->getUserGroup();
                $userGroupContentInfo = $userGroup->getVersionInfo()->getContentInfo();
                $userGroupLocation = $this->locationService->loadLocation($userGroupContentInfo->mainLocationId);
                $contentType = $this->contentTypeService->loadContentType($userGroupContentInfo->contentTypeId);

                $restUserGroups[] = new Values\RestUserGroup(
                    $userGroup,
                    $contentType,
                    $userGroupContentInfo,
                    $userGroupLocation,
                    $this->contentService->loadRelations($userGroup->getVersionInfo())
                );
            }
        }

        return $restUserGroups;
    }

    /**
     * Loads drafts assigned to user.
     */
    public function loadUserDrafts(int $userId, Request $request): Values\VersionList
    {
        $contentDrafts = $this->contentService->loadContentDrafts(
            $this->userService->loadUser($userId)
        );

        return new Values\VersionList($contentDrafts, $request->getPathInfo());
    }

    /**
     * Moves the user group to another parent.
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     * @throws \Ibexa\Core\Base\Exceptions\UnauthorizedException
     */
    public function moveUserGroup(string $groupPath, Request $request): Values\ResourceCreated
    {
        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($groupPath)
        );

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId
        );

        $locationPath = $this->requestParser->parseHref(
            $request->headers->get('Destination'),
            'groupPath'
        );

        try {
            $destinationGroupLocation = $this->locationService->loadLocation(
                $this->extractLocationIdFromPath($locationPath)
            );
        } catch (ApiExceptions\NotFoundException $e) {
            throw new Exceptions\ForbiddenException($e->getMessage());
        }

        try {
            $destinationGroup = $this->userService->loadUserGroup($destinationGroupLocation->contentId);
        } catch (ApiExceptions\NotFoundException $e) {
            throw new Exceptions\ForbiddenException($e->getMessage());
        }

        $this->userService->moveUserGroup($userGroup, $destinationGroup);

        return new Values\ResourceCreated(
            $this->router->generate(
                'ibexa.rest.load_user_group',
                [
                    'groupPath' => trim($destinationGroupLocation->pathString, '/') . '/' . $userGroupLocation->id,
                ]
            )
        );
    }

    /**
     * @throws \Ibexa\Contracts\Rest\Exceptions\ForbiddenException
     * @throws \Ibexa\Core\Base\Exceptions\UnauthorizedException
     */
    public function moveGroup(string $groupPath, Request $request): Values\ResourceCreated
    {
        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($groupPath)
        );

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId,
        );

        try {
            /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location $destinationLocation */
            $destinationLocation = $this->inputDispatcher->parse(
                new Message(
                    ['Content-Type' => $request->headers->get('Content-Type')],
                    $request->getContent(),
                ),
            );
        } catch (ApiExceptions\NotFoundException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage(), 1, $e);
        }

        $destinationGroup = $this->userService->loadUserGroup(
            $destinationLocation->getContent()->getId(),
        );

        $this->userService->moveUserGroup($userGroup, $destinationGroup);

        return new Values\ResourceCreated(
            $this->router->generate(
                'ibexa.rest.load_user_group',
                [
                    'groupPath' => trim($destinationLocation->pathString, '/')
                        . '/'
                        . $userGroupLocation->getId(),
                ],
            )
        );
    }

    /**
     * Returns a list of the sub groups.
     */
    public function loadSubUserGroups(string $groupPath, Request $request): RestValue
    {
        $offset = $request->query->has('offset') ? (int)$request->query->get('offset') : 0;
        $limit = $request->query->has('limit') ? (int)$request->query->get('limit') : 25;

        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($groupPath)
        );

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId
        );

        $subGroups = $this->userService->loadSubUserGroups(
            $userGroup,
            $offset >= 0 ? $offset : 0,
            $limit >= 0 ? $limit : 25,
            Language::ALL
        );

        $restUserGroups = [];
        foreach ($subGroups as $subGroup) {
            $subGroupContentInfo = $subGroup->getVersionInfo()->getContentInfo();
            $subGroupLocation = $this->locationService->loadLocation($subGroupContentInfo->mainLocationId);
            $contentType = $this->contentTypeService->loadContentType($subGroupContentInfo->contentTypeId);

            $restUserGroups[] = new Values\RestUserGroup(
                $subGroup,
                $contentType,
                $subGroupContentInfo,
                $subGroupLocation,
                $this->contentService->loadRelations($subGroup->getVersionInfo())
            );
        }

        if ($this->getMediaType($request) === 'application/vnd.ibexa.api.usergrouplist') {
            return new Values\CachedValue(
                new Values\UserGroupList($restUserGroups, $request->getPathInfo()),
                ['locationId' => $userGroupLocation->id]
            );
        }

        return new Values\CachedValue(
            new Values\UserGroupRefList($restUserGroups, $request->getPathInfo()),
            ['locationId' => $userGroupLocation->id]
        );
    }

    /**
     * Returns a list of user groups the user belongs to.
     * The returned list includes the resources for unassigning
     * a user group if the user is in multiple groups.
     */
    public function loadUserGroupsOfUser(int $userId, Request $request): RestValue
    {
        $offset = $request->query->has('offset') ? (int)$request->query->get('offset') : 0;
        $limit = $request->query->has('limit') ? (int)$request->query->get('limit') : 25;

        $user = $this->userService->loadUser($userId);
        $userGroups = $this->userService->loadUserGroupsOfUser(
            $user,
            $offset >= 0 ? $offset : 0,
            $limit >= 0 ? $limit : 25,
            Language::ALL
        );

        $restUserGroups = [];
        foreach ($userGroups as $userGroup) {
            $userGroupContentInfo = $userGroup->getVersionInfo()->getContentInfo();
            $userGroupLocation = $this->locationService->loadLocation($userGroupContentInfo->mainLocationId);
            $contentType = $this->contentTypeService->loadContentType($userGroupContentInfo->contentTypeId);

            $restUserGroups[] = new Values\RestUserGroup(
                $userGroup,
                $contentType,
                $userGroupContentInfo,
                $userGroupLocation,
                $this->contentService->loadRelations($userGroup->getVersionInfo())
            );
        }

        return new Values\CachedValue(
            new Values\UserGroupRefList($restUserGroups, $request->getPathInfo(), $userId),
            ['locationId' => $user->contentInfo->mainLocationId]
        );
    }

    /**
     * Loads the users of the group with the given path.
     */
    public function loadUsersFromGroup(string $groupPath, Request $request): RestValue
    {
        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($groupPath)
        );

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId
        );

        $offset = $request->query->has('offset') ? (int)$request->query->get('offset') : 0;
        $limit = $request->query->has('limit') ? (int)$request->query->get('limit') : 25;

        $users = $this->userService->loadUsersOfUserGroup(
            $userGroup,
            $offset >= 0 ? $offset : 0,
            $limit >= 0 ? $limit : 25,
            Language::ALL
        );

        $restUsers = [];
        foreach ($users as $user) {
            $userContentInfo = $user->getVersionInfo()->getContentInfo();
            $userLocation = $this->locationService->loadLocation($userContentInfo->mainLocationId);
            $contentType = $this->contentTypeService->loadContentType($userContentInfo->contentTypeId);

            $restUsers[] = new Values\RestUser(
                $user,
                $contentType,
                $userContentInfo,
                $userLocation,
                $this->contentService->loadRelations($user->getVersionInfo())
            );
        }

        if ($this->getMediaType($request) === 'application/vnd.ibexa.api.userlist') {
            return new Values\CachedValue(
                new Values\UserList($restUsers, $request->getPathInfo()),
                ['locationId' => $userGroupLocation->id]
            );
        }

        return new Values\CachedValue(
            new Values\UserRefList($restUsers, $request->getPathInfo()),
            ['locationId' => $userGroupLocation->id]
        );
    }

    /**
     * Unassigns the user from a user group.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     * @throws \Ibexa\Core\Base\Exceptions\UnauthorizedException
     */
    public function unassignUserFromUserGroup(int $userId, string $groupPath): Values\UserGroupRefList
    {
        $user = $this->userService->loadUser($userId);
        $userGroupLocation = $this->locationService->loadLocation((int)trim($groupPath, '/'));

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId
        );

        try {
            $this->userService->unAssignUserFromUserGroup($user, $userGroup);
        } catch (ApiExceptions\InvalidArgumentException $e) {
            // User is not in the group
            throw new Exceptions\ForbiddenException($e->getMessage());
        }

        $userGroups = $this->userService->loadUserGroupsOfUser($user);
        $restUserGroups = [];
        foreach ($userGroups as $userGroup) {
            $userGroupContentInfo = $userGroup->getVersionInfo()->getContentInfo();
            $userGroupLocation = $this->locationService->loadLocation($userGroupContentInfo->mainLocationId);
            $contentType = $this->contentTypeService->loadContentType($userGroupContentInfo->contentTypeId);

            $restUserGroups[] = new Values\RestUserGroup(
                $userGroup,
                $contentType,
                $userGroupContentInfo,
                $userGroupLocation,
                $this->contentService->loadRelations($userGroup->getVersionInfo())
            );
        }

        return new Values\UserGroupRefList(
            $restUserGroups,
            $this->router->generate(
                'ibexa.rest.load_user_groups_of_user',
                ['userId' => $userId]
            ),
            $userId
        );
    }

    /**
     * Assigns the user to a user group.
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     * @throws \Ibexa\Core\Base\Exceptions\UnauthorizedException
     */
    public function assignUserToUserGroup(int $userId, Request $request): Values\UserGroupRefList
    {
        $user = $this->userService->loadUser($userId);

        try {
            $userGroupLocation = $this->locationService->loadLocation(
                $this->extractLocationIdFromPath($request->query->get('group'))
            );
        } catch (ApiExceptions\NotFoundException $e) {
            throw new Exceptions\ForbiddenException($e->getMessage());
        }

        try {
            $userGroup = $this->userService->loadUserGroup(
                $userGroupLocation->contentId
            );
        } catch (ApiExceptions\NotFoundException $e) {
            throw new Exceptions\ForbiddenException($e->getMessage());
        }

        try {
            $this->userService->assignUserToUserGroup($user, $userGroup);
        } catch (ApiExceptions\NotFoundException $e) {
            throw new Exceptions\ForbiddenException($e->getMessage());
        }

        $userGroups = $this->userService->loadUserGroupsOfUser($user);
        $restUserGroups = [];
        foreach ($userGroups as $userGroup) {
            $userGroupContentInfo = $userGroup->getVersionInfo()->getContentInfo();
            $userGroupLocation = $this->locationService->loadLocation($userGroupContentInfo->mainLocationId);
            $contentType = $this->contentTypeService->loadContentType($userGroupContentInfo->contentTypeId);

            $restUserGroups[] = new Values\RestUserGroup(
                $userGroup,
                $contentType,
                $userGroupContentInfo,
                $userGroupLocation,
                $this->contentService->loadRelations($userGroup->getVersionInfo())
            );
        }

        return new Values\UserGroupRefList(
            $restUserGroups,
            $this->router->generate(
                'ibexa.rest.load_user_groups_of_user',
                ['userId' => $userId]
            ),
            $userId
        );
    }

    /**
     * Extracts and returns an item id from a path, e.g. /1/2/58 => 58.
     */
    private function extractLocationIdFromPath(string $path): int
    {
        $pathParts = explode('/', $path);

        return (int)array_pop($pathParts);
    }
}
