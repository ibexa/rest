<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Role;

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
class RoleAssignmentForUserGroupLoadByIdController extends RoleBaseController
{
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
}
