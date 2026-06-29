<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Controller\User;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/user/users',
    openapi: new Model\Operation(
        operationId: 'ibexa.rest.load_users',
        summary: 'List Users',
        description: 'Load Users either for a given role ID, remote ID, login, or email. To use one of the available filter is mandatory.',
        tags: [
            'User',
        ],
        parameters: [
            new Model\Parameter(
                name: 'roleId',
                in: 'query',
                description: 'Role directly assigned to the Users to load. (If the role is assigned to a user group, its users won\'t be returned. See GET /user/groups roleId filter.)',
                required: false,
                schema: [
                    'type' => 'string',
                    'description' => 'Reference path to the Role',
                    'example' => '/api/ibexa/v2/user/roles/2',
                ],
            ),
            new Model\Parameter(
                name: 'remoteId',
                in: 'query',
                description: 'Remote ID of the User to load.',
                required: false,
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'login',
                in: 'query',
                description: 'Username of the User to load.',
                required: false,
                schema: [
                    'type' => 'string',
                    'example' => 'admin',
                ],
            ),
            new Model\Parameter(
                name: 'email',
                in: 'query',
                description: 'Email address of the User to load.',
                required: false,
                schema: [
                    'type' => 'string',
                    'example' => 'admin@link.invalid',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - Loads Users either for a given remote ID or Role.',
                'content' => [
                    'application/vnd.ibexa.api.UserList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/GET/UserList.json.example',
                    ],
                    'application/vnd.ibexa.api.UserList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/GET/UserList.xml.example',
                    ],
                    'application/vnd.ibexa.api.UserRefList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserRefListWrapper',
                        ],
                    ],
                    'application/vnd.ibexa.api.UserRefList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserRefList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/GET/UserRefList.xml.example',
                    ],
                ],
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'If there are no visible Users matching the filter or the filter is missing.',
            ],
            Response::HTTP_NOT_ACCEPTABLE => [
                'description' => 'The filter value is not acceptable.',
            ],
        ],
    ),
)]
final class UserListController extends UserBaseController
{
}
