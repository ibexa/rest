<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Rest\Value as RestValue;

/**
 * Version list view model.
 */
class VersionList extends RestValue
{
    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo[]
     */
    public array $versions;

    /**
     * Path used to retrieve this version list.
     */
    public string $path;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo[] $versions
     */
    public function __construct(array $versions, string $path)
    {
        $this->versions = $versions;
        $this->path = $path;
    }
}
