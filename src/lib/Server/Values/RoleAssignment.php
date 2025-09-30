<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Rest\Server\Values;

use Ibexa\Contracts\Core\Repository\Values\User\Limitation\RoleLimitation;
use Ibexa\Rest\Value as RestValue;

/**
 * RoleAssignment view model.
 */
class RoleAssignment extends RestValue
{
    /**
     * Role ID.
     *
     * @var mixed
     */
    public $roleId;

    public ?RoleLimitation $limitation;

    /**
     * @param mixed $roleId
     */
    public function __construct($roleId, ?RoleLimitation $limitation = null)
    {
        $this->roleId = $roleId;
        $this->limitation = $limitation;
    }
}

class_alias(RoleAssignment::class, 'EzSystems\EzPlatformRest\Server\Values\RoleAssignment');
