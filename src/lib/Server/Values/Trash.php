<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Rest\Value as RestValue;

/**
 * Trash view model.
 */
class Trash extends RestValue
{
    /**
     * Trash items.
     *
     * @var RestTrashItem[]
     */
    public array $trashItems;

    /**
     * Path used to load the list of the trash items.
     */
    public string $path;

    /**
     * @param RestTrashItem[] $trashItems
     */
    public function __construct(
        array $trashItems,
        string $path
    ) {
        $this->trashItems = $trashItems;
        $this->path = $path;
    }
}
