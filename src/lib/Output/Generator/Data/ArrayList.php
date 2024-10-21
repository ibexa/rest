<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Output\Generator\Data;

final class ArrayList extends \ArrayObject
{
    private object $parent;

    private string $name;

    public function __construct(
        string $name,
        object $parent
    ) {
        $this->name = $name;
        $this->parent = $parent;

        parent::__construct();
    }

    public function getParent(): object
    {
        return $this->parent;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
