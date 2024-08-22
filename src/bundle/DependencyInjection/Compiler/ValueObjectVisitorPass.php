<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\Rest\DependencyInjection\Compiler;

use Ibexa\Contracts\Rest\Output\AdapterNormalizer;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitorDispatcher;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass for the ibexa.rest.output.value_object.visitor tag.
 * Maps an fully qualified class to a value object visitor.
 */
class ValueObjectVisitorPass implements CompilerPassInterface
{
    public const OUTPUT_VALUE_OBJECT_VISITOR_SERVICE_TAG = 'ibexa.rest.output.value_object.visitor';

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(ValueObjectVisitorDispatcher::class)) {
            return;
        }

        $definitions = [
            $container->getDefinition(ValueObjectVisitorDispatcher::class),
            $container->getDefinition(AdapterNormalizer::class),
        ];

        $taggedServiceIds = $container->findTaggedServiceIds(
            self::OUTPUT_VALUE_OBJECT_VISITOR_SERVICE_TAG
        );
        foreach ($definitions as $definition) {
            foreach ($taggedServiceIds as $id => $attributes) {
                foreach ($attributes as $attribute) {
                    if (!isset($attribute['type'])) {
                        throw new \LogicException(
                            sprintf(
                                'The "%s" service tag needs a "type" attribute to identify the field type.',
                                self::OUTPUT_VALUE_OBJECT_VISITOR_SERVICE_TAG
                            )
                        );
                    }

                    $definition->addMethodCall(
                        'addVisitor',
                        [$attribute['type'], new Reference($id)]
                    );
                }
            }
        }
    }
}
