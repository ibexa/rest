<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Rest\Value as RestValue;

class CachedValue extends RestValue
{
    /**
     * Actual value object.
     */
    public mixed $value;

    /**
     * Associative array of cache tags.
     * Example: array( 'locationId' => 59 ).
     *
     * @var array<string, mixed>
     */
    public array $cacheTags;

    /**
     * @param mixed $value The value that gets cached
     * @param array<string, mixed> $cacheTags Tags to add to the cache (supported: locationId)
     *
     * @throw InvalidArgumentException If invalid cache tags are provided
     */
    public function __construct(mixed $value, array $cacheTags = [])
    {
        $this->value = $value;
        $this->cacheTags = $this->checkCacheTags($cacheTags);
    }

    /**
     * @param array<string, mixed> $tags
     *
     * @return array<string, mixed>
     *
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentException
     */
    protected function checkCacheTags(array $tags): array
    {
        $invalidTags = array_diff(array_keys($tags), ['locationId']);
        if (count($invalidTags) > 0) {
            throw new InvalidArgumentException(
                'cacheTags',
                'Unknown cache tag(s): ' . implode(', ', $invalidTags)
            );
        }

        return $tags;
    }
}
