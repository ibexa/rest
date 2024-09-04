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
use Ibexa\Contracts\Core\Repository\Exceptions\LimitationValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException as APINotFoundException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\User\RoleCreateStruct;
use Ibexa\Contracts\Core\Repository\Values\User\RoleUpdateStruct;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Core\Base\Exceptions\ForbiddenException;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\Base\Exceptions\UnauthorizedException;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\BadRequestException;
use Ibexa\Rest\Server\Values;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/user/roles',
    name: 'Load Roles',
    openapi: new Model\Operation(
        summary: 'Returns a list of all Roles.',
        tags: [
            'User Role',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the user list returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - list of all Roles.',
                'content' => [
                    'application/vnd.ibexa.api.RoleList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RoleList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/GET/RoleList.xml.example',
                    ],
                    'application/vnd.ibexa.api.RoleList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RoleListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/GET/RoleList.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user has no permission to read Roles.',
            ],
        ],
    ),
)]
#[Post(
    uriTemplate: '/user/roles',
    name: 'Create Role or Role draft',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Creates a new Role or Role draft.',
        tags: [
            'User Role',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the new user is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The RoleInput schema encoded in XML or JSON.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.RoleInput+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/RoleInput',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/POST/RoleInput.xml.example',
                ],
                'application/vnd.ibexa.api.RoleInput+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/RoleInputWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/POST/RoleInput.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_CREATED => [
                'content' => [
                    'application/vnd.ibexa.api.Role+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Role',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/draft/PATCH/Role.xml.example',
                    ],
                    'application/vnd.ibexa.api.Role+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RoleWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/draft/PATCH/Role.json.example',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - the input does not match the input schema definition.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to create a Role or a Role draft.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/user/roles/{id}',
    name: 'Load Role',
    openapi: new Model\Operation(
        summary: 'Loads a Role for the given ID.',
        tags: [
            'User Role',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the user list returned in XML or JSON format.',
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
                name: 'id',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - Role for the given ID.',
                'content' => [
                    'application/vnd.ibexa.api.Role+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Role',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/draft/PATCH/Role.xml.example',
                    ],
                    'application/vnd.ibexa.api.Role+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RoleWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/draft/PATCH/Role.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user has no permission to read Roles.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the Role does not exist.',
            ],
        ],
    ),
)]
#[Post(
    uriTemplate: '/user/roles/{id}',
    name: 'Create Role Draft',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapiContext: ['requestBody' => false],
    openapi: new Model\Operation(
        summary: 'Creates a new Role draft from an existing Role.',
        tags: [
            'User Role',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the new user is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The RoleInput schema encoded in XML or JSON.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_CREATED => [
                'content' => [
                    'application/vnd.ibexa.api.RoleDraft+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RoleDraft',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/POST/RoleDraft.xml.example',
                    ],
                    'application/vnd.ibexa.api.RoleDraft+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RoleDraftWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/draft/PATCH/Role.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to create a Role or a Role draft',
            ],
        ],
    ),
)]
#[Patch(
    uriTemplate: '/user/roles/{id}',
    name: 'Update Role',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Updates a Role. PATCH or POST with header X-HTTP-Method-Override PATCH',
        tags: [
            'User Role',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the new user is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The RoleInput schema encoded in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'If-Match',
                in: 'header',
                required: true,
                description: 'ETag Causes to patch only if the specified ETag is the current one. Otherwise a 412 is returned.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.RoleInput+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/RoleInput',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/POST/RoleInput.xml.example',
                ],
                'application/vnd.ibexa.api.RoleInput+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/RoleInputWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/POST/RoleInput.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - Role updated',
                'content' => [
                    'application/vnd.ibexa.api.Role+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Role',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/draft/PATCH/Role.xml.example',
                    ],
                    'application/vnd.ibexa.api.Role+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RoleWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/draft/PATCH/Role.json.example',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - the input does not match the input schema definition.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to update the Role.',
            ],
            Response::HTTP_PRECONDITION_FAILED => [
                'description' => 'Error - the current ETag does not match with the provided one in the If-Match header.',
            ],
        ],
    ),
)]
#[Delete(
    uriTemplate: '/user/roles/{id}',
    name: 'Delete Role',
    openapi: new Model\Operation(
        summary: 'The given Role and all assignments to Users or User Groups are deleted.',
        tags: [
            'User Role',
        ],
        parameters: [
            new Model\Parameter(
                name: 'id',
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
                'description' => 'Error - the User is not authorized to delete this Role.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/user/roles/{id}/draft',
    name: 'Load Role draft',
    openapi: new Model\Operation(
        summary: 'Loads a Role draft by original Role ID.',
        tags: [
            'User Role',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the User list returned in XML or JSON format.',
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
                name: 'id',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - Role draft by original Role ID.',
                'content' => [
                    'application/vnd.ibexa.api.Role+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Role',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/draft/PATCH/Role.xml.example',
                    ],
                    'application/vnd.ibexa.api.Role+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RoleWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/draft/PATCH/Role.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user has no permission to read Roles.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - there is no draft or Role with the given ID.',
            ],
        ],
    ),
)]
#[Patch(
    uriTemplate: '/user/roles/{id}/draft',
    name: 'Update Role draft',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Updates a Role draft. PATCH or POST with header X-HTTP-Method-Override PATCH.',
        tags: [
            'User Role',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the updated Role is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The RoleInput schema encoded in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'If-Match',
                in: 'header',
                required: true,
                description: 'Performs a PATCH only if the specified ETag is the current one. Otherwise a 412 is returned.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.RoleInput+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/RoleInput',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/POST/RoleInput.xml.example',
                ],
                'application/vnd.ibexa.api.RoleInput+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/RoleInputWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/POST/RoleInput.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - Role draft updated.',
                'content' => [
                    'application/vnd.ibexa.api.Role+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Role',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/draft/PATCH/Role.xml.example',
                    ],
                    'application/vnd.ibexa.api.Role+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RoleWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/draft/PATCH/Role.json.example',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - the input does not match the input schema definition.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to update the Role.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - there is no draft or Role with the given ID.',
            ],
            Response::HTTP_PRECONDITION_FAILED => [
                'description' => 'Error - the current ETag does not match with the one provided in the If-Match header.',
            ],
        ],
    ),
)]
#[Delete(
    uriTemplate: '/user/roles/{id}/draft',
    name: 'Delete Role draft',
    openapi: new Model\Operation(
        summary: 'The given Role draft is deleted.',
        tags: [
            'User Role',
        ],
        parameters: [
            new Model\Parameter(
                name: 'id',
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
                'description' => 'Error - the user is not authorized to delete this Role.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/user/roles/{id}/policies',
    name: 'Load Policies',
    openapi: new Model\Operation(
        summary: 'Loads Policies for the given Role.',
        tags: [
            'User Role',
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
            new Model\Parameter(
                name: 'id',
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
                    'application/vnd.ibexa.api.PolicyList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/PolicyList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/policies/GET/PolicyList.xml.example',
                    ],
                    'application/vnd.ibexa.api.PolicyList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/PolicyListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/policies/GET/PolicyList.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user has no permission to read Roles.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the Role does not exist.',
            ],
        ],
    ),
)]
#[Delete(
    uriTemplate: '/user/roles/{id}/policies',
    name: 'Delete Policies',
    openapi: new Model\Operation(
        summary: 'All Policies of the given Role are deleted.',
        tags: [
            'User Role',
        ],
        parameters: [
            new Model\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'No Content - all Policies of the given Role are deleted.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to delete this content type.',
            ],
        ],
    ),
)]
#[Post(
    uriTemplate: '/user/roles/{id}/policies',
    name: 'Create Policy',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Creates a Policy',
        tags: [
            'User Role',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the updated Policy is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'If set, the updated Policy is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.PolicyCreate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/PolicyCreate',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/policies/POST/PolicyCreate.xml.example',
                ],
                'application/vnd.ibexa.api.PolicyCreate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/PolicyCreateWrapper',
                    ],
                ],
            ]),
        ),
        responses: [
            Response::HTTP_CREATED => [
                'content' => [
                    'application/vnd.ibexa.api.Policy+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Policy',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/policies/id/PATCH/Policy.xml.example',
                    ],
                    'application/vnd.ibexa.api.Policy+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/PolicyWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/policies/id/GET/Policy.json.example',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - the input does not match the input schema definition or validation of limitation in PolicyCreate fails.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to create the Policy.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the Role does not exist.',
            ],
        ],
    ),
)]
#[Patch(
    uriTemplate: '/user/roles/{id}/policies/{id}',
    name: 'Update Policy',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Updates a Policy. PATCH or POST with header X-HTTP-Method-Override PATCH.',
        tags: [
            'User Role',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the updated Policy is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'If set, the updated Policy is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'If-Match',
                in: 'header',
                required: true,
                description: 'Causes to patch only if the specified ETag is the current one. Otherwise a 412 is returned.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.PolicyUpdate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/PolicyUpdate',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/policies/id/PATCH/PolicyUpdate.xml.example',
                ],
                'application/vnd.ibexa.api.PolicyUpdate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/PolicyUpdateWrapper',
                    ],
                ],
            ]),
        ),
        responses: [
            Response::HTTP_OK => [
                'content' => [
                    'application/vnd.ibexa.api.Policy+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Policy',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/policies/id/PATCH/Policy.xml.example',
                    ],
                    'application/vnd.ibexa.api.Policy+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/PolicyWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/policies/id/GET/Policy.json.example',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - the input does not match the input schema definition or validation of limitation in PolicyUpdate fails.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to update the Policy.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the Role does not exist.',
            ],
            Response::HTTP_PRECONDITION_FAILED => [
                'description' => 'Error - the current ETag does not match with the one provided in the If-Match header.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/user/roles/{id}/policies/{id}',
    name: 'Load Policy',
    openapi: new Model\Operation(
        summary: 'Loads a Policy for the given module and function.',
        tags: [
            'User Role',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the Policy is returned in XML or JSON format.',
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
                name: 'id',
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
                    'application/vnd.ibexa.api.Policy+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Policy',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/policies/id/PATCH/Policy.xml.example',
                    ],
                    'application/vnd.ibexa.api.Policy+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/PolicyWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/policies/id/GET/Policy.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user has no permission to read Roles.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the Role or Policy does not exist.',
            ],
        ],
    ),
)]
#[Delete(
    uriTemplate: '/user/roles/{id}/policies/{id}',
    name: 'Delete Policy',
    openapi: new Model\Operation(
        summary: 'Deletes given Policy.',
        tags: [
            'User Role',
        ],
        parameters: [
            new Model\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'No Content - the given Policy is deleted.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to delete this content type.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the Role or Policy does not exist.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/user/groups/{path}/roles',
    name: 'Load Roles for User Group',
    openapi: new Model\Operation(
        summary: 'Returns a list of all Roles assigned to the given User Group.',
        tags: [
            'User Group',
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
                'content' => [
                    'application/vnd.ibexa.api.RoleAssignmentList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RoleAssignmentList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/roles/role_id/GET/RoleAssignment.xml.example',
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
    uriTemplate: '/user/groups/{path}/roles',
    name: 'Assign Role to User Group',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Assigns a Role to a User Group.',
        tags: [
            'User Group',
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
                'application/vnd.ibexa.api.RoleAssignInput+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/RoleAssignInput',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/groups/path/roles/POST/RoleAssignInput.xml.example',
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
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/roles/role_id/GET/RoleAssignment.xml.example',
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
    uriTemplate: '/user/groups/{path}/roles/{roleId}',
    name: 'Load User Group Role Assignment',
    openapi: new Model\Operation(
        summary: 'Returns a Role assignment of the given User Group.',
        tags: [
            'User Group',
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
                name: 'path',
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
                'description' => 'OK - returns a Role assignment of the given User Group.',
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
    uriTemplate: '/user/groups/{path}/roles/{roleId}',
    name: 'Unassign Role from User Group',
    openapi: new Model\Operation(
        summary: 'The given Role is removed from the User or User Group.',
        tags: [
            'User Group',
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
                name: 'path',
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
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/roles/role_id/GET/RoleAssignment.xml.example',
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
                'description' => 'Error - the user is not authorized to delete this Role assignment.',
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
            'User Policy',
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
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/policies/GET/PolicyList.xml.example',
                    ],
                    'application/vnd.ibexa.api.PolicyList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/PolicyListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/policies/GET/PolicyList.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user has no permission to read Roles.',
            ],
        ],
    ),
)]
/**
 * Role controller.
 */
class Role extends RestController
{
    /**
     * Role service.
     *
     * @var \Ibexa\Contracts\Core\Repository\RoleService
     */
    protected $roleService;

    /**
     * User service.
     *
     * @var \Ibexa\Contracts\Core\Repository\UserService
     */
    protected $userService;

    /**
     * Location service.
     *
     * @var \Ibexa\Contracts\Core\Repository\LocationService
     */
    protected $locationService;

    /**
     * Construct controller.
     *
     * @param \Ibexa\Contracts\Core\Repository\RoleService $roleService
     * @param \Ibexa\Contracts\Core\Repository\UserService $userService
     * @param \Ibexa\Contracts\Core\Repository\LocationService $locationService
     */
    public function __construct(
        RoleService $roleService,
        UserService $userService,
        LocationService $locationService
    ) {
        $this->roleService = $roleService;
        $this->userService = $userService;
        $this->locationService = $locationService;
    }

    /**
     * Create new role.
     *
     * Defaults to publishing the role, but you can create a draft instead by setting the POST parameter publish=false
     *
     * @return \Ibexa\Rest\Server\Values\CreatedRole
     */
    public function createRole(Request $request)
    {
        $publish = (
            !$request->query->has('publish') ||
            ($request->query->has('publish') && $request->query->get('publish') === 'true')
        );

        try {
            $roleDraft = $this->roleService->createRole(
                $this->inputDispatcher->parse(
                    new Message(
                        [
                            'Content-Type' => $request->headers->get('Content-Type'),
                            // @todo Needs refactoring! Temporary solution so parser has access to get parameters
                            '__publish' => $publish,
                        ],
                        $request->getContent()
                    )
                )
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        } catch (UnauthorizedException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        } catch (LimitationValidationException $e) {
            throw new BadRequestException($e->getMessage());
        } catch (Exceptions\Parser $e) {
            throw new BadRequestException($e->getMessage());
        }

        if ($publish) {
            @trigger_error(
                "Create and publish role in the same operation is deprecated, and will be removed in the future.\n" .
                'Instead, publish the role draft using Role::publishRoleDraft().',
                E_USER_DEPRECATED
            );

            $this->roleService->publishRoleDraft($roleDraft);

            $role = $this->roleService->loadRole($roleDraft->id);

            return new Values\CreatedRole(['role' => new Values\RestRole($role)]);
        }

        return new Values\CreatedRole(['role' => new Values\RestRole($roleDraft)]);
    }

    /**
     * Creates a new RoleDraft for an existing Role.
     *
     * @since 6.2
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ForbiddenException if the Role already has a Role Draft that will need to be removed first,
     *                                                                  or if the authenticated user is not allowed to create a role
     * @throws \Ibexa\Rest\Server\Exceptions\BadRequestException if a policy limitation in the $roleCreateStruct is not valid
     *
     * @return \Ibexa\Rest\Server\Values\CreatedRole
     */
    public function createRoleDraft($roleId, Request $request)
    {
        try {
            $roleDraft = $this->roleService->createRoleDraft(
                $this->roleService->loadRole($roleId)
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        } catch (UnauthorizedException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        } catch (LimitationValidationException $e) {
            throw new BadRequestException($e->getMessage());
        } catch (Exceptions\Parser $e) {
            throw new BadRequestException($e->getMessage());
        }

        return new Values\CreatedRole(['role' => new Values\RestRole($roleDraft)]);
    }

    /**
     * Loads list of roles.
     *
     * @return \Ibexa\Rest\Server\Values\RoleList
     */
    public function listRoles(Request $request)
    {
        $roles = [];
        if ($request->query->has('identifier')) {
            try {
                $role = $this->roleService->loadRoleByIdentifier($request->query->get('identifier'));
                $roles[] = $role;
            } catch (APINotFoundException $e) {
                // Do nothing
            }
        } else {
            $offset = $request->query->has('offset') ? (int)$request->query->get('offset') : 0;
            $limit = $request->query->has('limit') ? (int)$request->query->get('limit') : -1;

            $roles = array_slice(
                $this->roleService->loadRoles(),
                $offset >= 0 ? $offset : 0,
                $limit >= 0 ? $limit : null
            );
        }

        return new Values\RoleList($roles, $request->getPathInfo());
    }

    /**
     * Loads role.
     *
     * @param $roleId
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\User\Role
     */
    public function loadRole($roleId)
    {
        return $this->roleService->loadRole($roleId);
    }

    /**
     * Loads a role draft.
     *
     * @param mixed $roleId Original role ID, or ID of the role draft itself
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\User\RoleDraft
     */
    public function loadRoleDraft($roleId)
    {
        try {
            // First try to load the draft for given role.
            return $this->roleService->loadRoleDraftByRoleId($roleId);
        } catch (NotFoundException $e) {
            // We might want a newly created role, so try to load it by its ID.
            // loadRoleDraft() might throw a NotFoundException (wrong $roleId). If so, let it bubble up.
            return $this->roleService->loadRoleDraft($roleId);
        }
    }

    /**
     * Updates a role.
     *
     * @param $roleId
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\User\Role
     */
    public function updateRole($roleId, Request $request)
    {
        $createStruct = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );
        $roleDraft = $this->roleService->createRoleDraft(
            $this->roleService->loadRole($roleId)
        );
        $roleDraft = $this->roleService->updateRoleDraft(
            $roleDraft,
            $this->mapToUpdateStruct($createStruct)
        );

        $this->roleService->publishRoleDraft($roleDraft);

        return $this->roleService->loadRole($roleId);
    }

    /**
     * Updates a role draft.
     *
     * @param mixed $roleId Original role ID, or ID of the role draft itself
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\User\RoleDraft
     */
    public function updateRoleDraft($roleId, Request $request)
    {
        $createStruct = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        try {
            // First try to load the draft for given role.
            $roleDraft = $this->roleService->loadRoleDraftByRoleId($roleId);
        } catch (NotFoundException $e) {
            // We might want a newly created role, so try to load it by its ID.
            // loadRoleDraft() might throw a NotFoundException (wrong $roleId). If so, let it bubble up.
            $roleDraft = $this->roleService->loadRoleDraft($roleId);
        }

        return $this->roleService->updateRoleDraft($roleDraft, $this->mapToUpdateStruct($createStruct));
    }

    /**
     * Publishes a role draft.
     *
     * @param mixed $roleId Original role ID, or ID of the role draft itself
     *
     * @return \Ibexa\Rest\Server\Values\PublishedRole
     */
    public function publishRoleDraft($roleId)
    {
        try {
            // First try to load the draft for given role.
            $roleDraft = $this->roleService->loadRoleDraftByRoleId($roleId);
        } catch (NotFoundException $e) {
            // We might want a newly created role, so try to load it by its ID.
            // loadRoleDraft() might throw a NotFoundException (wrong $roleId). If so, let it bubble up.
            $roleDraft = $this->roleService->loadRoleDraft($roleId);
        }

        $this->roleService->publishRoleDraft($roleDraft);

        $role = $this->roleService->loadRoleByIdentifier($roleDraft->identifier);

        return new Values\PublishedRole(['role' => new Values\RestRole($role)]);
    }

    /**
     * Delete a role by ID.
     *
     * @param $roleId
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function deleteRole($roleId)
    {
        $this->roleService->deleteRole(
            $this->roleService->loadRole($roleId)
        );

        return new Values\NoContent();
    }

    /**
     * Delete a role draft by ID.
     *
     * @since 6.2
     *
     * @param $roleId
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function deleteRoleDraft($roleId)
    {
        $this->roleService->deleteRoleDraft(
            $this->roleService->loadRoleDraft($roleId)
        );

        return new Values\NoContent();
    }

    /**
     * Loads the policies for the role.
     *
     * @param $roleId
     *
     * @return \Ibexa\Rest\Server\Values\PolicyList
     */
    public function loadPolicies($roleId, Request $request)
    {
        $loadedRole = $this->roleService->loadRole($roleId);

        return new Values\PolicyList($loadedRole->getPolicies(), $request->getPathInfo());
    }

    /**
     * Deletes all policies from a role.
     *
     * @param $roleId
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function deletePolicies($roleId)
    {
        $loadedRole = $this->roleService->loadRole($roleId);
        $roleDraft = $this->roleService->createRoleDraft($loadedRole);
        /** @var \Ibexa\Contracts\Core\Repository\Values\User\PolicyDraft $policyDraft */
        foreach ($roleDraft->getPolicies() as $policyDraft) {
            $this->roleService->removePolicyByRoleDraft($roleDraft, $policyDraft);
        }
        $this->roleService->publishRoleDraft($roleDraft);

        return new Values\NoContent();
    }

    /**
     * Loads a policy.
     *
     * @param $roleId
     * @param $policyId
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\User\Policy
     */
    public function loadPolicy($roleId, $policyId, Request $request)
    {
        $loadedRole = $this->roleService->loadRole($roleId);
        foreach ($loadedRole->getPolicies() as $policy) {
            if ($policy->id == $policyId) {
                return $policy;
            }
        }

        throw new Exceptions\NotFoundException("Policy not found: '{$request->getPathInfo()}'.");
    }

    /**
     * Adds a policy to role.
     *
     * @param int $roleId ID of a role draft
     *
     * @return \Ibexa\Rest\Server\Values\CreatedPolicy
     */
    public function addPolicy($roleId, Request $request)
    {
        $createStruct = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        try {
            // First try to treat $roleId as a role draft ID.
            $role = $this->roleService->addPolicyByRoleDraft(
                $this->roleService->loadRoleDraft($roleId),
                $createStruct
            );
        } catch (NotFoundException $e) {
            // Then try to treat $roleId as a role ID.
            $roleDraft = $this->roleService->createRoleDraft(
                $this->roleService->loadRole($roleId)
            );
            $roleDraft = $this->roleService->addPolicyByRoleDraft(
                $roleDraft,
                $createStruct
            );
            $this->roleService->publishRoleDraft($roleDraft);
            $role = $this->roleService->loadRole($roleId);
        } catch (LimitationValidationException $e) {
            throw new BadRequestException($e->getMessage());
        }

        return new Values\CreatedPolicy(
            [
                'policy' => $this->getLastAddedPolicy($role),
            ]
        );
    }

    /**
     * Get the last added policy for $role.
     *
     * This is needed because the RoleService addPolicy() and addPolicyByRoleDraft() methods return the role,
     * not the policy.
     *
     * @param $role \Ibexa\Contracts\Core\Repository\Values\User\Role
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\User\Policy
     */
    private function getLastAddedPolicy($role)
    {
        $policies = $role->getPolicies();

        $policyToReturn = $policies[0];
        for ($i = 1, $count = count($policies); $i < $count; ++$i) {
            if ($policies[$i]->id > $policyToReturn->id) {
                $policyToReturn = $policies[$i];
            }
        }

        return $policyToReturn;
    }

    /**
     * Updates a policy.
     *
     * @param int $roleId ID of a role draft
     * @param int $policyId ID of a policy
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\User\Policy
     */
    public function updatePolicy($roleId, $policyId, Request $request)
    {
        $updateStruct = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );
        try {
            // First try to treat $roleId as a role draft ID.
            $roleDraft = $this->roleService->loadRoleDraft($roleId);
            foreach ($roleDraft->getPolicies() as $policy) {
                if ($policy->id == $policyId) {
                    try {
                        return $this->roleService->updatePolicyByRoleDraft(
                            $roleDraft,
                            $policy,
                            $updateStruct
                        );
                    } catch (LimitationValidationException $e) {
                        throw new BadRequestException($e->getMessage());
                    }
                }
            }
        } catch (NotFoundException $e) {
            // Then try to treat $roleId as a role ID.
            $roleDraft = $this->roleService->createRoleDraft(
                $this->roleService->loadRole($roleId)
            );
            foreach ($roleDraft->getPolicies() as $policy) {
                if ($policy->originalId == $policyId) {
                    try {
                        $policyDraft = $this->roleService->updatePolicyByRoleDraft(
                            $roleDraft,
                            $policy,
                            $updateStruct
                        );
                        $this->roleService->publishRoleDraft($roleDraft);
                        $role = $this->roleService->loadRole($roleId);

                        foreach ($role->getPolicies() as $newPolicy) {
                            if ($newPolicy->id == $policyDraft->id) {
                                return $newPolicy;
                            }
                        }
                    } catch (LimitationValidationException $e) {
                        throw new BadRequestException($e->getMessage());
                    }
                }
            }
        }

        throw new Exceptions\NotFoundException("Policy not found: '{$request->getPathInfo()}'.");
    }

    /**
     * Delete a policy from role.
     *
     * @param int $roleId ID of a role draft
     * @param int $policyId ID of a policy
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function deletePolicy($roleId, $policyId, Request $request)
    {
        try {
            // First try to treat $roleId as a role draft ID.
            $roleDraft = $this->roleService->loadRoleDraft($roleId);
            $policy = null;
            foreach ($roleDraft->getPolicies() as $rolePolicy) {
                if ($rolePolicy->id == $policyId) {
                    $policy = $rolePolicy;
                    break;
                }
            }
            if ($policy !== null) {
                $this->roleService->removePolicyByRoleDraft($roleDraft, $policy);

                return new Values\NoContent();
            }
        } catch (NotFoundException $e) {
            // Then try to treat $roleId as a role ID.
            $roleDraft = $this->roleService->createRoleDraft(
                $this->roleService->loadRole($roleId)
            );
            $policy = null;
            foreach ($roleDraft->getPolicies() as $rolePolicy) {
                if ($rolePolicy->originalId == $policyId) {
                    $policy = $rolePolicy;
                    break;
                }
            }
            if ($policy !== null) {
                $this->roleService->removePolicyByRoleDraft($roleDraft, $policy);
                $this->roleService->publishRoleDraft($roleDraft);

                return new Values\NoContent();
            }
        }
        throw new Exceptions\NotFoundException("Policy not found: '{$request->getPathInfo()}'.");
    }

    /**
     * Assigns role to user.
     *
     * @param $userId
     *
     * @return \Ibexa\Rest\Server\Values\RoleAssignmentList
     */
    public function assignRoleToUser($userId, Request $request)
    {
        $roleAssignment = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        $user = $this->userService->loadUser($userId);
        $role = $this->roleService->loadRole($roleAssignment->roleId);

        try {
            $this->roleService->assignRoleToUser($role, $user, $roleAssignment->limitation);
        } catch (LimitationValidationException $e) {
            throw new BadRequestException($e->getMessage());
        }

        $roleAssignments = $this->roleService->getRoleAssignmentsForUser($user);

        return new Values\RoleAssignmentList($roleAssignments, $user->id);
    }

    /**
     * Assigns role to user group.
     *
     * @param $groupPath
     *
     * @return \Ibexa\Rest\Server\Values\RoleAssignmentList
     */
    public function assignRoleToUserGroup($groupPath, Request $request)
    {
        $roleAssignment = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        $groupLocationParts = explode('/', $groupPath);
        $groupLocation = $this->locationService->loadLocation(array_pop($groupLocationParts));
        $userGroup = $this->userService->loadUserGroup($groupLocation->contentId);

        $role = $this->roleService->loadRole($roleAssignment->roleId);

        try {
            $this->roleService->assignRoleToUserGroup($role, $userGroup, $roleAssignment->limitation);
        } catch (LimitationValidationException $e) {
            throw new BadRequestException($e->getMessage());
        }

        $roleAssignments = $this->roleService->getRoleAssignmentsForUserGroup($userGroup);

        return new Values\RoleAssignmentList($roleAssignments, $groupPath, true);
    }

    /**
     * Un-assigns role from user.
     *
     * @param $userId
     * @param $roleId
     *
     * @return \Ibexa\Rest\Server\Values\RoleAssignmentList
     */
    public function unassignRoleFromUser($userId, $roleId)
    {
        $user = $this->userService->loadUser($userId);

        $roleAssignments = $this->roleService->getRoleAssignmentsForUser($user);
        foreach ($roleAssignments as $roleAssignment) {
            if ($roleAssignment->role->id == $roleId) {
                $this->roleService->removeRoleAssignment($roleAssignment);
            }
        }
        $newRoleAssignments = $this->roleService->getRoleAssignmentsForUser($user);

        return new Values\RoleAssignmentList($newRoleAssignments, $user->id);
    }

    /**
     * Un-assigns role from user group.
     *
     * @param $groupPath
     * @param $roleId
     *
     * @return \Ibexa\Rest\Server\Values\RoleAssignmentList
     */
    public function unassignRoleFromUserGroup($groupPath, $roleId)
    {
        $groupLocationParts = explode('/', $groupPath);
        $groupLocation = $this->locationService->loadLocation(array_pop($groupLocationParts));
        $userGroup = $this->userService->loadUserGroup($groupLocation->contentId);

        $roleAssignments = $this->roleService->getRoleAssignmentsForUserGroup($userGroup);
        foreach ($roleAssignments as $roleAssignment) {
            if ($roleAssignment->role->id == $roleId) {
                $this->roleService->removeRoleAssignment($roleAssignment);
            }
        }
        $roleAssignments = $this->roleService->getRoleAssignmentsForUserGroup($userGroup);

        return new Values\RoleAssignmentList($roleAssignments, $groupPath, true);
    }

    /**
     * Loads role assignments for user.
     *
     * @param $userId
     *
     * @return \Ibexa\Rest\Server\Values\RoleAssignmentList
     */
    public function loadRoleAssignmentsForUser($userId)
    {
        $user = $this->userService->loadUser($userId);

        $roleAssignments = $this->roleService->getRoleAssignmentsForUser($user);

        return new Values\RoleAssignmentList($roleAssignments, $user->id);
    }

    /**
     * Loads role assignments for user group.
     *
     * @param $groupPath
     *
     * @return \Ibexa\Rest\Server\Values\RoleAssignmentList
     */
    public function loadRoleAssignmentsForUserGroup($groupPath)
    {
        $groupLocationParts = explode('/', $groupPath);
        $groupLocation = $this->locationService->loadLocation(array_pop($groupLocationParts));
        $userGroup = $this->userService->loadUserGroup($groupLocation->contentId);

        $roleAssignments = $this->roleService->getRoleAssignmentsForUserGroup($userGroup);

        return new Values\RoleAssignmentList($roleAssignments, $groupPath, true);
    }

    /**
     * Returns a role assignment to the given user.
     *
     * @param $userId
     * @param $roleId
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     *
     * @return \Ibexa\Rest\Server\Values\RestUserRoleAssignment
     */
    public function loadRoleAssignmentForUser($userId, $roleId, Request $request)
    {
        $user = $this->userService->loadUser($userId);
        $roleAssignments = $this->roleService->getRoleAssignmentsForUser($user);

        foreach ($roleAssignments as $roleAssignment) {
            if ($roleAssignment->getRole()->id == $roleId) {
                return new Values\RestUserRoleAssignment($roleAssignment, $userId);
            }
        }

        throw new Exceptions\NotFoundException("Role assignment not found: '{$request->getPathInfo()}'.");
    }

    /**
     * Returns a role assignment to the given user group.
     *
     * @param $groupPath
     * @param $roleId
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     *
     * @return \Ibexa\Rest\Server\Values\RestUserGroupRoleAssignment
     */
    public function loadRoleAssignmentForUserGroup($groupPath, $roleId, Request $request)
    {
        $groupLocationParts = explode('/', $groupPath);
        $groupLocation = $this->locationService->loadLocation(array_pop($groupLocationParts));
        $userGroup = $this->userService->loadUserGroup($groupLocation->contentId);

        $roleAssignments = $this->roleService->getRoleAssignmentsForUserGroup($userGroup);
        foreach ($roleAssignments as $roleAssignment) {
            if ($roleAssignment->getRole()->id == $roleId) {
                return new Values\RestUserGroupRoleAssignment($roleAssignment, $groupPath);
            }
        }

        throw new Exceptions\NotFoundException("Role assignment not found: '{$request->getPathInfo()}'.");
    }

    /**
     * Search all policies which are applied to a given user.
     *
     * @return \Ibexa\Rest\Server\Values\PolicyList
     */
    public function listPoliciesForUser(Request $request)
    {
        $user = $this->userService->loadUser((int)$request->query->get('userId'));
        $roleAssignments = $this->roleService->getRoleAssignmentsForUser($user, true);

        $policies = [];
        foreach ($roleAssignments as $roleAssignment) {
            $policies[] = $roleAssignment->getRole()->getPolicies();
        }

        return new Values\PolicyList(
            !empty($policies) ? array_merge(...$policies) : [],
            $request->getPathInfo()
        );
    }

    /**
     * Maps a RoleCreateStruct to a RoleUpdateStruct.
     *
     * Needed since both structs are encoded into the same media type on input.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\User\RoleCreateStruct $createStruct
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\User\RoleUpdateStruct
     */
    protected function mapToUpdateStruct(RoleCreateStruct $createStruct)
    {
        return new RoleUpdateStruct(
            [
                'identifier' => $createStruct->identifier,
            ]
        );
    }
}
