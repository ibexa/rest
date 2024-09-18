<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Role;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Delete(
    uriTemplate: '/user/roles/{id}/policies/{id}',
    name: 'Delete Policy',
    openapi: new Model\Operation(
        summary: 'Deletes given Policy.',
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
                'description' => 'No Content - the given Policy is deleted.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to delete this content type.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the Role or Policy does not exist.',
            ],
        ],
    ),
)]
class RolePolicyDeleteController extends RoleBaseController
{
    /**
     * Delete a policy from role.
     *
     * @param int $roleId ID of a role draft
     * @param int $policyId ID of a policy
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function deletePolicy($roleId, $policyId, Request $request)
    {
        try {
            // First try to treat $roleId as a role draft ID.
            $roleDraft = $this->roleService->loadRoleDraft($roleId);
            $policy = null;
            foreach ($roleDraft->getPolicies() as $rolePolicy) {
                if ($rolePolicy->id == $policyId) {
                    $policy = $rolePolicy;
                    break;
                }
            }
            if ($policy !== null) {
                $this->roleService->removePolicyByRoleDraft($roleDraft, $policy);

                return new Values\NoContent();
            }
        } catch (NotFoundException $e) {
            // Then try to treat $roleId as a role ID.
            $roleDraft = $this->roleService->createRoleDraft(
                $this->roleService->loadRole($roleId)
            );
            $policy = null;
            foreach ($roleDraft->getPolicies() as $rolePolicy) {
                if ($rolePolicy->originalId == $policyId) {
                    $policy = $rolePolicy;
                    break;
                }
            }
            if ($policy !== null) {
                $this->roleService->removePolicyByRoleDraft($roleDraft, $policy);
                $this->roleService->publishRoleDraft($roleDraft);

                return new Values\NoContent();
            }
        }
        throw new Exceptions\NotFoundException("Policy not found: '{$request->getPathInfo()}'.");
    }
}