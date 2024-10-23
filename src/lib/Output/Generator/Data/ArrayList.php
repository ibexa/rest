<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Output\Generator\Data;

use Ibexa\Rest\Output\Generator\Json\ArrayObject;
use Ibexa\Rest\Output\Generator\Json\JsonObject;

final class ArrayList extends \ArrayObject
{
    private self|JsonObject|ArrayObject|null $parent;

    private string $name;

    public function __construct(
        string $name,
        self|JsonObject|ArrayObject|null $parent,
    ) {
        $this->name = $name;
        $this->parent = $parent;

        parent::__construct();
    }

    public function getParent(): self|JsonObject|ArrayObject|null
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
