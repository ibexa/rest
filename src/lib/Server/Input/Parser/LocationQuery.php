<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery as LocationQueryValueObject;
use Ibexa\Rest\Server\Input\Parser\Query as QueryParser;

/**
 * Parser for LocationQuery.
 */
class LocationQuery extends QueryParser
{
    protected function buildQuery(): LocationQueryValueObject
    {
        return new LocationQueryValueObject();
    }
}
