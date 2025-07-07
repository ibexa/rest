<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Values;

use Ibexa\Rest\Value as RestValue;

/**
 * This class represents a resource.
 */
class Resource extends RestValue
{
    public string $name;

    public string $mediaType;

    public string $href;

    public function __construct(string $name, string $mediaType, string $href)
    {
        $this->name = $name;
        $this->mediaType = $mediaType;
        $this->href = $href;
    }
}
