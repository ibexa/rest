<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Rest\Value as RestValue;

/**
 * URLAlias ref list view model.
 */
class URLAliasRefList extends RestValue
{
    /**
     * URL aliases.
     *
     * @var \Ibexa\Contracts\Core\Repository\Values\Content\URLAlias[]
     */
    public array $urlAliases;

    /**
     * Path that was used to fetch the list of URL aliases.
     */
    public string $path;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\URLAlias[] $urlAliases
     */
    public function __construct(array $urlAliases, string $path)
    {
        $this->urlAliases = $urlAliases;
        $this->path = $path;
    }
}
