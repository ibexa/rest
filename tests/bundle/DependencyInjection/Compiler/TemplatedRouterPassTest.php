<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\Rest\DependencyInjection\Compiler;

use Ibexa\Bundle\Rest\DependencyInjection\Compiler\TemplatedRouterPass;
use LogicException;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class TemplatedRouterPassTest extends TestCase
{
    /**
     * @param array<string, mixed> $defaultRouterOptions
     * @param array<string, mixed> $expectedOptions
     */
    #[TestWith([['resource_type' => 'service'], ['cache_dir' => '/cache', 'resource_type' => 'service']])]
    #[TestWith([[], ['cache_dir' => '/cache']])]
    public function testCopiesResourceTypeFromDefaultRouter(
        array $defaultRouterOptions,
        array $expectedOptions
    ): void {
        $container = $this->buildContainer($defaultRouterOptions);

        (new TemplatedRouterPass())->process($container);

        self::assertSame(
            $expectedOptions,
            $container->getDefinition(TemplatedRouterPass::TEMPLATED_ROUTER_SERVICE_ID)->getArgument('$options')
        );
    }

    public function testThrowsWhenDefaultRouterOptionsAreNotAnArray(): void
    {
        $container = $this->buildContainer('not-an-array');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Expected the options argument (index 2) of the "router.default" service to be an array, "string" given.');

        (new TemplatedRouterPass())->process($container);
    }

    private function buildContainer(mixed $defaultRouterOptions): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setDefinition('router.default', new Definition(null, [null, 'resource', $defaultRouterOptions]));
        $container->setDefinition(
            TemplatedRouterPass::TEMPLATED_ROUTER_SERVICE_ID,
            new Definition(null, ['$options' => ['cache_dir' => '/cache']])
        );

        return $container;
    }
}
