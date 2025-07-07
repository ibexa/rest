<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Rest\Value as RestValue;

/**
 * Role list view model.
 */
class RoleList extends RestValue
{
    /**
     * Roles.
     *
     * @var \Ibexa\Contracts\Core\Repository\Values\User\Role[]
     */
    public array $roles;

    /**
     * Path used to load the list of roles.
     */
    public string $path;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\User\Role[] $roles
     */
    public function __construct(array $roles, string $path)
    {
        $this->roles = $roles;
        $this->path = $path;
    }
}
