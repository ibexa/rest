<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\Rest\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class LexikAuthorizationHeaderBridgePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('lexik_jwt_authentication.extractor.authorization_header_extractor')) {
            return;
        }

        $definition = $container->getDefinition('lexik_jwt_authentication.extractor.authorization_header_extractor');
        $headerName = $definition->getArgument(1);

        $container->setParameter('ibexa.rest.authorization_header_name', $headerName);
    }
}
