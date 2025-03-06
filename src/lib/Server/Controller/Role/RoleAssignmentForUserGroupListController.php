<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Role;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/user/groups/{path}/roles',
    name: 'Load Roles for User Group',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
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
class RoleAssignmentForUserGroupListController extends RoleBaseController
{
    /**
     * Loads role assignments for user group.
     */
    public function loadRoleAssignmentsForUserGroup(string $groupPath): \Ibexa\Rest\Server\Values\RoleAssignmentList
    {
        $groupLocationParts = explode('/', $groupPath);
        $groupLocation = $this->locationService->loadLocation((int)array_pop($groupLocationParts));
        $userGroup = $this->userService->loadUserGroup($groupLocation->contentId);

        $roleAssignmentsIterable = $this->roleService->getRoleAssignmentsForUserGroup($userGroup);
        $roleAssignments = [];
        foreach ($roleAssignmentsIterable as $roleAssignment) {
            $roleAssignments[] = $roleAssignment;
        }

        return new Values\RoleAssignmentList($roleAssignments, $groupPath, true);
    }
}
