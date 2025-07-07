<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Values;

use Ibexa\Rest\Value as RestValue;

class BookmarkList extends RestValue
{
    public int $totalCount = 0;

    /**
     * @var \Ibexa\Rest\Server\Values\RestLocation[]
     */
    public array $items = [];

    /**
     * BookmarkList constructor.
     *
     * @param \Ibexa\Rest\Server\Values\RestLocation[] $items
     */
    public function __construct(int $totalCount, array $items)
    {
        $this->totalCount = $totalCount;
        $this->items = $items;
    }
}
