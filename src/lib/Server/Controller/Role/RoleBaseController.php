<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Role;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\User\Policy;
use Ibexa\Contracts\Core\Repository\Values\User\Role;
use Ibexa\Contracts\Core\Repository\Values\User\RoleCreateStruct;
use Ibexa\Contracts\Core\Repository\Values\User\RoleUpdateStruct;
use Ibexa\Rest\Server\Controller as RestController;

class RoleBaseController extends RestController
{
    protected RoleService $roleService;

    protected UserService $userService;

    protected LocationService $locationService;

    public function __construct(
        RoleService $roleService,
        UserService $userService,
        LocationService $locationService
    ) {
        $this->roleService = $roleService;
        $this->userService = $userService;
        $this->locationService = $locationService;
    }

    /**
     * Get the last added policy for $role.
     *
     * This is needed because the RoleService addPolicy() and addPolicyByRoleDraft() methods return the role,
     * not the policy.
     */
    protected function getLastAddedPolicy(Role $role): Policy
    {
        $policiesIterable = $role->getPolicies();
        $policies = [];
        foreach ($policiesIterable as $policy) {
            $policies[] = $policy;
        }

        $policyToReturn = $policies[0];
        for ($i = 1, $count = count($policies); $i < $count; ++$i) {
            if ($policies[$i]->id > $policyToReturn->id) {
                $policyToReturn = $policies[$i];
            }
        }

        return $policyToReturn;
    }

    /**
     * Maps a RoleCreateStruct to a RoleUpdateStruct.
     *
     * Needed since both structs are encoded into the same media type on input.
     */
    protected function mapToUpdateStruct(RoleCreateStruct $createStruct): RoleUpdateStruct
    {
        return new RoleUpdateStruct(
            [
                'identifier' => $createStruct->identifier,
            ]
        );
    }
}
