<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\Rest;

use Ibexa\Bundle\Rest\DependencyInjection\Compiler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class IbexaRestBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new Compiler\FieldTypeProcessorPass());
        $container->addCompilerPass(new Compiler\InputHandlerPass());
        $container->addCompilerPass(new Compiler\InputParserPass());
        $container->addCompilerPass(new Compiler\OutputVisitorPass());
        $container->addCompilerPass(new Compiler\ValueObjectVisitorPass());

        if ($container->hasExtension('lexik_jwt_authentication')) {
            $container->addCompilerPass(new Compiler\LexikAuthorizationHeaderBridgePass());
        }
    }
}
