<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Rest\Value as RestValue;

/**
 * RestLocation view model.
 */
class RestLocation extends RestValue
{
    public Location $location;

    /**
     * Number of children of the location.
     */
    public int $childCount;

    public function __construct(Location $location, int $childCount)
    {
        $this->location = $location;
        $this->childCount = $childCount;
    }
}
