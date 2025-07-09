<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Service;

use Ibexa\Rest\Values\Root;

interface RootResourceBuilderInterface
{
    public function buildRootResource(): Root;
}
