<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\Rest\DependencyInjection\Compiler;

use Ibexa\Bundle\Rest\DependencyInjection\Compiler\TemplatedRouterPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class TemplatedRouterPassTest extends TestCase
{
    public function testCopiesResourceTypeFromDefaultRouter(): void
    {
        $container = $this->buildContainer(['resource_type' => 'service']);

        (new TemplatedRouterPass())->process($container);

        self::assertSame(
            ['cache_dir' => '/cache', 'resource_type' => 'service'],
            $container->getDefinition(TemplatedRouterPass::TEMPLATED_ROUTER_SERVICE_ID)->getArgument(2)
        );
    }

    public function testKeepsOptionsWhenDefaultRouterHasNoResourceType(): void
    {
        $container = $this->buildContainer([]);

        (new TemplatedRouterPass())->process($container);

        self::assertSame(
            ['cache_dir' => '/cache'],
            $container->getDefinition(TemplatedRouterPass::TEMPLATED_ROUTER_SERVICE_ID)->getArgument(2)
        );
    }

    /**
     * @param array<string, mixed> $defaultRouterOptions
     */
    private function buildContainer(array $defaultRouterOptions): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setDefinition('router.default', new Definition(null, [null, 'resource', $defaultRouterOptions]));
        $container->setDefinition(
            TemplatedRouterPass::TEMPLATED_ROUTER_SERVICE_ID,
            new Definition(null, [null, 'resource', ['cache_dir' => '/cache']])
        );

        return $container;
    }
}
