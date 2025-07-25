<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Contracts\Core\Repository\Values\User\Role;
use Ibexa\Contracts\Core\Repository\Values\User\RoleDraft;
use Ibexa\Rest\Value as RestValue;

/**
 * REST Role, as received by /roles/<ID>.
 */
class RestRole extends RestValue
{
    /**
     * Holds internal role object.
     */
    public Role|RoleDraft $innerRole;

    public function __construct(Role|RoleDraft $role)
    {
        $this->innerRole = $role;
    }

    /**
     * Magic getter for routing get calls to innerRole.
     */
    public function __get(string $property): mixed
    {
        return $this->innerRole->$property;
    }

    /**
     * Magic set for routing set calls to innerRole.
     */
    public function __set(string $property, mixed $propertyValue): void
    {
        $this->innerRole->$property = $propertyValue;
    }

    /**
     * Magic isset for routing isset calls to innerRole.
     */
    public function __isset(string $property): bool
    {
        return $this->innerRole->__isset($property);
    }
}
