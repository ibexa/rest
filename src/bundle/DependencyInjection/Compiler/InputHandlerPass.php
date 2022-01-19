<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Bundle\Rest\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Container processor for the ibexa.rest.input.handler service tag.
 * Maps input formats (json, xml) to handlers.
 *
 * Tag attributes: format. Ex: json
 */
class InputHandlerPass implements CompilerPassInterface
{
    public const INPUT_HANDLER_SERVICE_TAG = 'ibexa.rest.input.handler';

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(\Ibexa\Rest\Input\Dispatcher::class)) {
            return;
        }

        $definition = $container->getDefinition(\Ibexa\Rest\Input\Dispatcher::class);

        $taggedServiceIds = $container->findTaggedServiceIds(self::INPUT_HANDLER_SERVICE_TAG);
        foreach ($taggedServiceIds as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['format'])) {
                    throw new \LogicException(
                        sprintf(
                            'The "%s" service tag needs a "format" attribute to identify the input handler.',
                            self::INPUT_HANDLER_SERVICE_TAG
                        )
                    );
                }

                $definition->addMethodCall(
                    'addHandler',
                    [$attribute['format'], new Reference($id)]
                );
            }
        }
    }
}

class_alias(InputHandlerPass::class, 'EzSystems\EzPlatformRestBundle\DependencyInjection\Compiler\InputHandlerPass');
