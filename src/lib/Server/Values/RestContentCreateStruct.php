<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentCreateStruct;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationCreateStruct;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;

/**
 * RestContentCreateStruct view model.
 */
class RestContentCreateStruct extends ValueObject
{
    public ContentCreateStruct $contentCreateStruct;

    public LocationCreateStruct $locationCreateStruct;

    public function __construct(ContentCreateStruct $contentCreateStruct, LocationCreateStruct $locationCreateStruct)
    {
        $this->contentCreateStruct = $contentCreateStruct;
        $this->locationCreateStruct = $locationCreateStruct;

        parent::__construct();
    }
}
