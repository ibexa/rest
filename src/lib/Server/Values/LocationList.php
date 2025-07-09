<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Rest\Value as RestValue;

/**
 * Location list view model.
 */
class LocationList extends RestValue
{
    /**
     * Locations.
     *
     * @var \Ibexa\Rest\Server\Values\RestLocation[]
     */
    public array $locations;

    /**
     * Path used to load this list of locations.
     */
    public string $path;

    /**
     * @param \Ibexa\Rest\Server\Values\RestLocation[] $locations
     */
    public function __construct(array $locations, string $path)
    {
        $this->locations = $locations;
        $this->path = $path;
    }
}
