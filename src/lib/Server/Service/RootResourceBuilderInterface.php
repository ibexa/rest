<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Service;

use Ibexa\Rest\Values\Root;

interface RootResourceBuilderInterface
{
    /**
     * Build root resource.
     *
     * @return array|Root
     */
    public function buildRootResource();
}

class_alias(RootResourceBuilderInterface::class, 'EzSystems\EzPlatformRest\Server\Service\RootResourceBuilderInterface');
