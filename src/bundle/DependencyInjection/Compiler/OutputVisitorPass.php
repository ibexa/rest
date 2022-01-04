<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Bundle\Rest\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass for the ibexa.rest.output.visitor tag.
 *
 * Maps an output visitor (json, xml...) to an accept-header
 *
 * @todo The tag is much more limited in scope than the name shows. Refactor. More ways to map ?
 */
class OutputVisitorPass implements CompilerPassInterface
{
    public const OUTPUT_VISITOR_SERVICE_TAG = 'ibexa.rest.output.visitor';

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezpublish_rest.output.visitor.dispatcher')) {
            return;
        }

        $definition = $container->getDefinition('ezpublish_rest.output.visitor.dispatcher');

        $visitors = [];

        $taggedServiceIds = $container->findTaggedServiceIds(self::OUTPUT_VISITOR_SERVICE_TAG);
        foreach ($taggedServiceIds as $id => $attributes) {
            foreach ($attributes as $attribute) {
                $priority = $attribute['priority'] ?? 0;

                if (!isset($attribute['regexps'])) {
                    throw new \LogicException(
                        sprintf(
                            'The "%s" service tag needs a "regexps" attribute to identify the Accept header.',
                            self::OUTPUT_VISITOR_SERVICE_TAG
                        )
                    );
                }

                if (is_array($attribute['regexps'])) {
                    $regexps = $attribute['regexps'];
                } elseif (is_string($attribute['regexps'])) {
                    try {
                        $regexps = $container->getParameter($attribute['regexps']);
                    } catch (InvalidArgumentException $e) {
                        throw new \LogicException("The regexps attribute of the ezpublish_rest.output.visitor service tag can be a string matching a container parameter name. Could not find parameter {$attribute['regexps']}.");
                    }
                } else {
                    throw new \LogicException('The ezpublish_rest.output.visitor service tag needs a "regexps" attribute, either as an array or a string. Invalid value.');
                }

                $visitors[$priority][] = [
                    'regexps' => $regexps,
                    'reference' => new Reference($id),
                ];
            }
        }

        // sort by priority and flatten
        krsort($visitors);
        $visitors = array_merge(...$visitors);

        foreach ($visitors as $visitor) {
            foreach ($visitor['regexps'] as $regexp) {
                $definition->addMethodCall(
                    'addVisitor',
                    [
                        $regexp,
                        $visitor['reference'],
                    ]
                );
            }
        }
    }
}

class_alias(OutputVisitorPass::class, 'EzSystems\EzPlatformRestBundle\DependencyInjection\Compiler\OutputVisitorPass');
