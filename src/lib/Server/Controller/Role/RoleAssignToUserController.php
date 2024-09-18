<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Role;

use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\Exceptions\LimitationValidationException;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Exceptions\BadRequestException;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
class RoleAssignToUserController extends RoleBaseController
{
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
}
