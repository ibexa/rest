<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Rest\Value as RestValue;

/**
 * RestContentCreateStruct view model.
 */
class RestViewInput extends RestValue
{
    public Query $query;

    public string $identifier;

    public ?string $languageCode;

    public ?bool $useAlwaysAvailable;
}
