<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\Rest\DependencyInjection\Compiler;

use LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Copies the routing resource type from the default router onto the templated one, so both load routes
 * the same way (e.g. "kernel::loadRoutes" needs "service" type).
 *
 * @internal
 */
final readonly class TemplatedRouterPass implements CompilerPassInterface
{
    public const string TEMPLATED_ROUTER_SERVICE_ID = 'ibexa.rest.templated_router.inner';

    public function process(ContainerBuilder $container): void
    {
        $defaultRouterOptions = $container->findDefinition('router.default')->getArgument(2);
        if (!is_array($defaultRouterOptions)) {
            throw new LogicException(sprintf(
                'Expected the options argument (index 2) of the "router.default" service to be an array, "%s" given.',
                get_debug_type($defaultRouterOptions)
            ));
        }

        // Set by FrameworkBundle only when "framework.router.type" is configured
        if (!isset($defaultRouterOptions['resource_type'])) {
            return;
        }

        $templatedRouter = $container->getDefinition(self::TEMPLATED_ROUTER_SERVICE_ID);
        $options = $templatedRouter->getArgument('$options');
        $options['resource_type'] = $defaultRouterOptions['resource_type'];
        $templatedRouter->replaceArgument('$options', $options);
    }
}
