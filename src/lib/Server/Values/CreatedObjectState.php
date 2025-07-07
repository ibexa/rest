<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Ibexa\Rest\Values\RestObjectState;

/**
 * Struct representing a freshly created object state.
 */
class CreatedObjectState extends ValueObject
{
    public RestObjectState $objectState;
}
