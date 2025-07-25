<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Values;

use Ibexa\Rest\Value as RestValue;

/**
 * This class represents the root resource.
 */
class Root extends RestValue
{
    /**
     * @var resource[]
     */
    protected array $resources;

    public function __construct(array $resources = [])
    {
        $this->resources = $resources;
    }

    /**
     * @return resource[]
     */
    public function getResources(): array
    {
        return $this->resources;
    }
}
