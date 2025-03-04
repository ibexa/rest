<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Role;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\OpenApi\Model;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Response;

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
class RoleUnassignFromUserController extends RoleBaseController
{
    /**
     * Un-assigns role from user.
     */
    public function unassignRoleFromUser(int $userId, int $roleId): Values\RoleAssignmentList
    {
        $user = $this->userService->loadUser($userId);

        $roleAssignments = $this->roleService->getRoleAssignmentsForUser($user);
        foreach ($roleAssignments as $roleAssignment) {
            if ($roleAssignment->role->id == $roleId) {
                $this->roleService->removeRoleAssignment($roleAssignment);
            }
        }
        $newRoleAssignmentsIterable = $this->roleService->getRoleAssignmentsForUser($user);
        $newRoleAssignments = [];
        foreach ($newRoleAssignmentsIterable as $roleAssignment) {
            $newRoleAssignments[] = $roleAssignment;
        }

        return new Values\RoleAssignmentList($newRoleAssignments, $user->id);
    }
}
