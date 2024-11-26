<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\Rest\DependencyInjection\Compiler;

use Ibexa\Contracts\Rest\Output\ValueObjectVisitorResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass for the ibexa.rest.output.value_object.visitor tag.
 * Maps a fully qualified class to a value object visitor.
 */
final readonly class ValueObjectVisitorResolverPass implements CompilerPassInterface
{
    public const string OUTPUT_VALUE_OBJECT_VISITOR_SERVICE_TAG = 'ibexa.rest.output.value_object.visitor';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(ValueObjectVisitorResolver::class)) {
            return;
        }

        $definition = $container->getDefinition(ValueObjectVisitorResolver::class);

        $taggedServiceIds = $container->findTaggedServiceIds(
            self::OUTPUT_VALUE_OBJECT_VISITOR_SERVICE_TAG
        );

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
