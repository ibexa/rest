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
class RoleDeleteController extends RoleBaseController
{
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
}
