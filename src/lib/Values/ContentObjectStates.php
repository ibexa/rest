<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Values;

use Ibexa\Rest\Value as RestValue;

class ContentObjectStates extends RestValue
{
    public array $states;

    public function __construct(array $states)
    {
        $this->states = $states;
    }
}
