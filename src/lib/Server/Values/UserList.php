<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Rest\Value as RestValue;

/**
 * User list view model.
 */
class UserList extends RestValue
{
    /**
     * @var \Ibexa\Rest\Server\Values\RestUser[]
     */
    public array $users;

    /**
     * Path which was used to fetch the list of users.
     */
    public string $path;

    /**
     * @param \Ibexa\Rest\Server\Values\RestUser[] $users
     */
    public function __construct(array $users, string $path)
    {
        $this->users = $users;
        $this->path = $path;
    }
}
