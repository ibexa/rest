<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\Rest\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ClassNameResourceNamePass implements CompilerPassInterface
{
    public const API_PLATFORM_CLASS_NAME_RESOURCE_SERVICE_TAG = 'ibexa.rest.api_platform.class_name_resource';

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ibexa.api_platform.metadata.resource.name_collection_factory')) {
            return;
        }

        $definition = $container->getDefinition('ibexa.api_platform.metadata.resource.name_collection_factory');

        $definition->addMethodCall(
            'addResources',
            [[
                'Ibexa\\Rest\\Server\\Controller\\Language',
            ]]
        );
//        $taggedServiceIds = $container->findTaggedServiceIds(self::INPUT_HANDLER_SERVICE_TAG);
//        foreach ($taggedServiceIds as $id => $attributes) {
//            $definition->addMethodCall(
//                'addResources',
//                [[
//                    'Ibexa\\Rest\\Server\\Controller\\Language',
//                ]]
//            );
//        }
    }
}
