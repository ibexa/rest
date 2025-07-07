<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Contracts\Core\Repository\Values\Content\LocationUpdateStruct;
use Ibexa\Rest\Value as RestValue;

/**
 * RestLocationUpdateStruct view model.
 */
class RestLocationUpdateStruct extends RestValue
{
    public LocationUpdateStruct $locationUpdateStruct;

    /**
     * If set, the location is hidden ( == true ) or unhidden ( == false ).
     */
    public ?bool $hidden;

    public function __construct(LocationUpdateStruct $locationUpdateStruct, ?bool $hidden = null)
    {
        $this->locationUpdateStruct = $locationUpdateStruct;
        $this->hidden = $hidden;
    }
}
