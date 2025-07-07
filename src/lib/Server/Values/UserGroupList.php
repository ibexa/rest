<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Rest\Value as RestValue;

/**
 * User group list view model.
 */
class UserGroupList extends RestValue
{
    /**
     * User groups.
     *
     * @var \Ibexa\Rest\Server\Values\RestUserGroup[]
     */
    public array $userGroups;

    /**
     * Path which was used to fetch the list of user groups.
     */
    public string $path;

    /**
     * @param \Ibexa\Rest\Server\Values\RestUserGroup[] $userGroups
     */
    public function __construct(array $userGroups, string $path)
    {
        $this->userGroups = $userGroups;
        $this->path = $path;
    }
}
