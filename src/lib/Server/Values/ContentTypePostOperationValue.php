<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Values;

use Ibexa\Rest\Value as RestValue;

final class ContentTypePostOperationValue extends RestValue
{
    public function __construct(
        private readonly string $operation,
        private readonly mixed $data = null,
    ) {
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function getData(): mixed
    {
        return $this->data;
    }
}
