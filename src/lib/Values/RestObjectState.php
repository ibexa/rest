<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Values;

use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState;
use Ibexa\Rest\Value as RestValue;

/**
 * This class wraps the object state with added groupId property.
 */
class RestObjectState extends RestValue
{
    /**
     * Wrapped object state.
     */
    public ObjectState $objectState;

    /**
     * Group ID to which wrapped state belongs.
     */
    public string|int $groupId;

    /**
     * Constructor.
     *
     * @param mixed $groupId
     */
    public function __construct(ObjectState $objectState, string|int $groupId)
    {
        $this->objectState = $objectState;
        $this->groupId = $groupId;
    }
}
