<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Contracts\Core\Repository\Values\User\RoleAssignment;
use Ibexa\Rest\Value as RestValue;

/**
 * RoleAssignment list view model.
 */
class RoleAssignmentList extends RestValue
{
    /**
     * Role assignments.
     *
     * @var RoleAssignment[]
     */
    public array $roleAssignments;

    /**
     * User or user group ID to which the roles are assigned.
     */
    public int | string $id;

    /**
     * Indicator if the role assignment is for user group.
     */
    public bool $isGroupAssignment;

    /**
     * @param RoleAssignment[] $roleAssignments
     */
    public function __construct(
        array $roleAssignments,
        string | int $id,
        bool $isGroupAssignment = false
    ) {
        $this->roleAssignments = $roleAssignments;
        $this->id = $id;
        $this->isGroupAssignment = $isGroupAssignment;
    }
}
