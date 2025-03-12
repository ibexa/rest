<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Role;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Response;

#[Delete(
    uriTemplate: '/user/roles/{id}/policies',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Delete Policies',
        description: 'All Policies of the given Role are deleted.',
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
class RolePolicyDeleteAllFromRoleController extends RoleBaseController
{
    /**
     * Deletes all policies from a role.
     */
    public function deletePolicies(int $roleId): \Ibexa\Rest\Server\Values\NoContent
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
}
