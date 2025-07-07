<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Rest\Value as RestValue;

/**
 * ObjectState list view model.
 */
class ObjectStateList extends RestValue
{
    /**
     * Object states.
     *
     * @var \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState[]
     */
    public array $states;

    /**
     * ID of the group that the states belong to.
     */
    public int|string $groupId;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState[] $states
     */
    public function __construct(array $states, int|string $groupId)
    {
        $this->states = $states;
        $this->groupId = $groupId;
    }
}
