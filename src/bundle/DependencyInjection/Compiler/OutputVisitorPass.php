<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Bundle\Rest\DependencyInjection\Compiler;

use Ibexa\Rest\Server\View\AcceptHeaderVisitorDispatcher;
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
        if (!$container->hasDefinition(AcceptHeaderVisitorDispatcher::class)) {
            return;
        }

        $definition = $container->getDefinition(AcceptHeaderVisitorDispatcher::class);

        $visitors = [];

        $taggedServiceIds = $container->findTaggedServiceIds(self::OUTPUT_VISITOR_SERVICE_TAG);
        foreach ($taggedServiceIds as $serviceId => $attributes) {
            foreach ($attributes as $attribute) {
                $priority = $attribute['priority'] ?? 0;
                $regexps = $attribute['regexps'];
                if (is_string($regexps)) {
                    try {
                        $regexps = $container->getParameter($regexps);
                    } catch (InvalidArgumentException $e) {
                        throw new \LogicException(
                            sprintf(
                                'Service "%s" tagged with "%s" service tag "regexps" attribute can be a string matching a container parameter name. Could not find parameter "%s".',
                                $serviceId,
                                self::OUTPUT_VISITOR_SERVICE_TAG,
                                $regexps
                            )
                        );
                    }
                } elseif (!is_array($regexps)) {
                    throw new \LogicException(
                        sprintf(
                            'Service "%s" tagged with "%s" service tag needs a "regexps" attribute to identify the Accept header, either as an array or a string.',
                            $serviceId,
                            self::OUTPUT_VISITOR_SERVICE_TAG
                        )
                    );
                }

                $visitors[$priority][] = [
                    'regexps' => $regexps,
                    'reference' => new Reference($serviceId),
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
