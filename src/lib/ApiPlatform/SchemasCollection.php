<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\ApiPlatform;

final readonly class SchemasCollection implements \IteratorAggregate, \Countable
{
    /**
     * @param array<string, mixed> $schemas
     */
    public function __construct(
        private array $schemas = [],
    ) {
    }

    /**
     * @return \Traversable<string>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->schemas);
    }

    public function count(): int
    {
        return \count($this->schemas);
    }
}
