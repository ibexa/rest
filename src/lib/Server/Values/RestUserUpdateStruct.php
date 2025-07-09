<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Contracts\Core\Repository\Values\User\UserUpdateStruct;
use Ibexa\Rest\Value as RestValue;

/**
 * RestUserUpdateStruct view model.
 */
class RestUserUpdateStruct extends RestValue
{
    public UserUpdateStruct $userUpdateStruct;

    /**
     * If set, section of the User will be updated.
     */
    public ?int $sectionId;

    public function __construct(UserUpdateStruct $userUpdateStruct, ?int $sectionId = null)
    {
        $this->userUpdateStruct = $userUpdateStruct;
        $this->sectionId = $sectionId;
    }
}
