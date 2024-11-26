<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Output\Generator\Data;

use ArrayObject;

final class ArrayList extends ArrayObject implements DataObjectInterface
{
    private ?DataObjectInterface $parent;

    private string $name;

    public function __construct(string $name, ?DataObjectInterface $parent)
    {
        $this->name = $name;
        $this->parent = $parent;

        parent::__construct();
    }

    public function getParent(): ?DataObjectInterface
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
