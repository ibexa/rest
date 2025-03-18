<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Role;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Rest\Server\Values;
use Ibexa\Rest\Server\Values\PublishedRole;

class RoleDraftPublishController extends RoleBaseController
{
    /**
     * Publishes a role draft.
     *
     * @param mixed $roleId Original role ID, or ID of the role draft itself
     *
     * @return \Ibexa\Rest\Server\Values\PublishedRole
     */
    public function publishRoleDraft(int $roleId): PublishedRole
    {
        try {
            // First try to load the draft for given role.
            $roleDraft = $this->roleService->loadRoleDraftByRoleId($roleId);
        } catch (NotFoundException $e) {
            // We might want a newly created role, so try to load it by its ID.
            // loadRoleDraft() might throw a NotFoundException (wrong $roleId). If so, let it bubble up.
            $roleDraft = $this->roleService->loadRoleDraft($roleId);
        }

        $this->roleService->publishRoleDraft($roleDraft);

        $role = $this->roleService->loadRoleByIdentifier($roleDraft->identifier);

        return new PublishedRole(['role' => new Values\RestRole($role)]);
    }
}
