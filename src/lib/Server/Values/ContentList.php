<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Rest\Value as RestValue;

/**
 * Content list view model.
 */
class ContentList extends RestValue
{
    /**
     * @var \Ibexa\Rest\Server\Values\RestContent[]
     */
    public array $contents;

    /**
     * Total items list count.
     */
    public int $totalCount;

    /**
     * Construct.
     *
     * @param \Ibexa\Rest\Server\Values\RestContent[] $contents
     */
    public function __construct(array $contents, int $totalCount)
    {
        $this->contents = $contents;
        $this->totalCount = $totalCount;
    }
}
