<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Contracts\Core\Repository\Values\User\UserGroupRoleAssignment;
use Ibexa\Rest\Value as RestValue;

/**
 * RestUserGroupRoleAssignment view model.
 */
class RestUserGroupRoleAssignment extends RestValue
{
    public UserGroupRoleAssignment $roleAssignment;

    /**
     * User group path to which the role is assigned.
     */
    public string|int $id;

    public function __construct(UserGroupRoleAssignment $roleAssignment, string|int $id)
    {
        $this->roleAssignment = $roleAssignment;
        $this->id = $id;
    }
}
