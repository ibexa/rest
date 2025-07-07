<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Contracts\Core\Repository\Values\User\UserGroupUpdateStruct;
use Ibexa\Rest\Value as RestValue;

/**
 * RestUserGroupUpdateStruct view model.
 */
class RestUserGroupUpdateStruct extends RestValue
{
    public UserGroupUpdateStruct $userGroupUpdateStruct;

    /**
     * If set, section of the UserGroup will be updated.
     */
    public ?int $sectionId;

    /**
     * Construct.
     *
     * @param mixed $sectionId
     */
    public function __construct(UserGroupUpdateStruct $userGroupUpdateStruct, ?int $sectionId = null)
    {
        $this->userGroupUpdateStruct = $userGroupUpdateStruct;
        $this->sectionId = $sectionId;
    }
}
