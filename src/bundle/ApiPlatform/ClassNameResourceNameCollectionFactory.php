<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace Ibexa\Bundle\Rest\ApiPlatform;

use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceNameCollection;

/**
 * @internal
 */
final class ClassNameResourceNameCollectionFactory implements ResourceNameCollectionFactoryInterface
{
    /**
     * @var array<string>
     */
    private array $resources = [];

    public function create(): ResourceNameCollection
    {
        return new ResourceNameCollection($this->resources);
    }

    /**
     * @param array<string> $newResources
     */
    public function addResources(array $newResources): void
    {
        $this->resources = array_merge($this->resources, $newResources);
    }
}
