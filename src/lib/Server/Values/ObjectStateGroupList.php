<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup;
use Ibexa\Rest\Value as RestValue;

/**
 * ObjectStateGroup list view model.
 */
class ObjectStateGroupList extends RestValue
{
    /**
     * Object state groups.
     *
     * @var ObjectStateGroup[]
     */
    public $groups;

    /**
     * Construct.
     *
     * @param ObjectStateGroup[] $groups
     */
    public function __construct(array $groups)
    {
        $this->groups = $groups;
    }
}

class_alias(ObjectStateGroupList::class, 'EzSystems\EzPlatformRest\Server\Values\ObjectStateGroupList');
