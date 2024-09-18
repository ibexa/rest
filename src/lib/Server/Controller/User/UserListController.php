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
final class UserListController extends UserBaseController
{
}
