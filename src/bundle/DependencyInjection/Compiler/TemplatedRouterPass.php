<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\Rest\DependencyInjection\Compiler;

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
        if (!$container->hasDefinition('router.default') || !$container->hasDefinition(self::TEMPLATED_ROUTER_SERVICE_ID)) {
            return;
        }

        $resourceType = $container->getDefinition('router.default')->getArgument(2)['resource_type'] ?? null;
        if ($resourceType === null) {
            return;
        }

        $templatedRouter = $container->getDefinition(self::TEMPLATED_ROUTER_SERVICE_ID);
        $options = $templatedRouter->getArgument('$options');
        $options['resource_type'] = $resourceType;
        $templatedRouter->replaceArgument('$options', $options);
    }
}
