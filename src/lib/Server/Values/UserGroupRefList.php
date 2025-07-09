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
class UserGroupRefList extends RestValue
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
     * User ID whose groups are the ones in the list.
     */
    public int|string|null $userId;

    /**
     * @param \Ibexa\Rest\Server\Values\RestUserGroup[] $userGroups
     */
    public function __construct(array $userGroups, string $path, int|string|null $userId = null)
    {
        $this->userGroups = $userGroups;
        $this->path = $path;
        $this->userId = $userId;
    }
}
