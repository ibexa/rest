<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\Rest\ApiPlatform;

use Ibexa\Rest\ApiPlatform\SchemasCollection;
use Ibexa\Rest\ApiPlatform\SchemasCollectionFactoryInterface;
use Ibexa\Rest\ApiPlatform\SchemasProviderInterface;

/**
 * @internal
 */
final class SchemasCollectionFactory implements SchemasCollectionFactoryInterface
{
    /**
     * @var array<SchemasProviderInterface>
     */
    private array $providers = [];

    public function create(): SchemasCollection
    {
        $schemas = [];

        foreach ($this->providers as $provider) {
            $schemas = array_merge($schemas, $provider->getSchemas());
        }

        return new SchemasCollection($schemas);
    }

    public function addProvider(SchemasProviderInterface $provider): void
    {
        $this->providers[] = $provider;
    }
}
