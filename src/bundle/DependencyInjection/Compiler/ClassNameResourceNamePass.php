<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\Rest\DependencyInjection\Compiler;

use Ibexa\Bundle\Rest\ApiPlatform\ClassNameResourceNameCollectionFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ClassNameResourceNamePass implements CompilerPassInterface
{
    public const API_PLATFORM_RESOURCE_SERVICE_TAG = 'ibexa.api_platform.resource';

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(ClassNameResourceNameCollectionFactory::class)) {
            return;
        }

        $definition = $container->getDefinition(ClassNameResourceNameCollectionFactory::class);

        $taggedServiceIds = $container->findTaggedServiceIds(self::API_PLATFORM_RESOURCE_SERVICE_TAG);
        foreach ($taggedServiceIds as $id => $attributes) {
            $taggedServiceDefinition = $container->getDefinition($id);
            $definition->addMethodCall(
                'addResources',
                [[$taggedServiceDefinition->getClass()]]
            );
        }
    }
}
