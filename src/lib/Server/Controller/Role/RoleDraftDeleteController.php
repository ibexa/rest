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
class RoleDraftDeleteController extends RoleBaseController
{
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
}
