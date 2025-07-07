<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Contracts\Core\Repository\Values\User\UserRoleAssignment;
use Ibexa\Rest\Value as RestValue;

/**
 * RestUserRoleAssignment view model.
 */
class RestUserRoleAssignment extends RestValue
{
    public UserRoleAssignment $roleAssignment;

    /**
     * User ID to which the role is assigned.
     */
    public int|string $id;

    public function __construct(UserRoleAssignment $roleAssignment, int|string $id)
    {
        $this->roleAssignment = $roleAssignment;
        $this->id = $id;
    }
}
