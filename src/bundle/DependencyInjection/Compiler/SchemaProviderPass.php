<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\Rest\DependencyInjection\Compiler;

use Ibexa\Bundle\Rest\ApiPlatform\SchemasCollectionFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final readonly class SchemaProviderPass implements CompilerPassInterface
{
    public const string API_PLATFORM_SCHEMA_PROVIDER_SERVICE_TAG = 'ibexa.api_platform.schemas_provider';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(SchemasCollectionFactory::class)) {
            return;
        }

        $definition = $container->getDefinition(SchemasCollectionFactory::class);

        $taggedServiceIds = $container->findTaggedServiceIds(self::API_PLATFORM_SCHEMA_PROVIDER_SERVICE_TAG);
        foreach ($taggedServiceIds as $serviceId => $attributes) {
            $definition->addMethodCall(
                'addProvider',
                [new Reference($serviceId)],
            );
        }
    }
}
