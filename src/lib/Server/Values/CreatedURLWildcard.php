<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Contracts\Core\Repository\Values\Content\URLWildcard;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;

/**
 * Struct representing a freshly created URLWildcard.
 */
class CreatedURLWildcard extends ValueObject
{
    public URLWildcard $urlWildcard;
}
