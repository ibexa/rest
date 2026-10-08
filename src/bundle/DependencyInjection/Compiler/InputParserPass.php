<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\Rest\DependencyInjection\Compiler;

use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Container compiler processor for the ibexa.rest.input.parser service tag.
 * Maps input parsers to media types.
 *
 * Tag attributes: mediaType. Ex: application/vnd.ibexa.api.Content
 */
class InputParserPass implements CompilerPassInterface
{
    public const INPUT_PARSER_SERVICE_TAG = 'ibexa.rest.input.parser';

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(ParsingDispatcher::class)) {
            return;
        }

        $definition = $container->getDefinition(ParsingDispatcher::class);

        $taggedServiceIds = $container->findTaggedServiceIds(self::INPUT_PARSER_SERVICE_TAG);
        foreach ($taggedServiceIds as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['mediaType'])) {
                    throw new \LogicException(
                        sprintf(
                            'The "%s" service tag needs a "mediaType" attribute to identify the input parser.',
                            self::INPUT_PARSER_SERVICE_TAG
                        )
                    );
                }

                $definition->addMethodCall(
                    'addParser',
                    [$attribute['mediaType'], new Reference($id)]
                );
            }
        }
    }
}

class_alias(InputParserPass::class, 'EzSystems\EzPlatformRestBundle\DependencyInjection\Compiler\InputParserPass');
