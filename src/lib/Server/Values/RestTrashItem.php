<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Contracts\Core\Repository\Values\Content\TrashItem;
use Ibexa\Rest\Value as RestValue;

/**
 * RestTrashItem view model.
 */
class RestTrashItem extends RestValue
{
    public TrashItem $trashItem;

    /**
     * Number of children of the trash item.
     */
    public int $childCount;

    public function __construct(TrashItem $trashItem, int $childCount)
    {
        $this->trashItem = $trashItem;
        $this->childCount = $childCount;
    }
}
