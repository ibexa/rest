<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Role;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Rest\Server\Values\RoleAssignmentList;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/user/users/{userId}/roles',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Load Roles for User',
        description: 'Returns a list of all Roles assigned to the given User.',
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
class RoleAssignmentForUserListController extends RoleBaseController
{
    /**
     * Loads role assignments for user.
     */
    public function loadRoleAssignmentsForUser(int $userId): RoleAssignmentList
    {
        $user = $this->userService->loadUser($userId);

        $roleAssignmentsIterable = $this->roleService->getRoleAssignmentsForUser($user);
        $roleAssignments = [];
        foreach ($roleAssignmentsIterable as $roleAssignment) {
            $roleAssignments[] = $roleAssignment;
        }

        return new RoleAssignmentList($roleAssignments, $user->id);
    }
}
