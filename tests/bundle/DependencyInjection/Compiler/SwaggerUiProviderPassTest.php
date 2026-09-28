<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\Rest\DependencyInjection\Compiler;

use Ibexa\Bundle\Rest\DependencyInjection\Compiler\SwaggerUiProviderPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

#[CoversClass(SwaggerUiProviderPass::class)]
final class SwaggerUiProviderPassTest extends TestCase
{
    public function testRegistersSwaggerUiProviderWhenApiPlatformProvidesIt(): void
    {
        $container = $this->buildContainer();
        $container->setDefinition('api_platform.swagger_ui.provider', new Definition());

        (new SwaggerUiProviderPass())->process($container);

        $swaggerUiProvider = $container->getDefinition(SwaggerUiProviderPass::SWAGGER_UI_PROVIDER_SERVICE_ID);
        self::assertInstanceOf(ChildDefinition::class, $swaggerUiProvider);
        self::assertSame('api_platform.swagger_ui.provider', $swaggerUiProvider->getParent());
        self::assertEquals(
            new Reference('ibexa.api_platform.ibexa_openapi.factory'),
            $swaggerUiProvider->getArgument(1)
        );
        self::assertEquals(
            new Reference(SwaggerUiProviderPass::SWAGGER_UI_PROVIDER_SERVICE_ID),
            $this->getContentNegotiationProvider($container)->getArgument(0)
        );
    }

    public function testKeepsReadProviderWhenApiPlatformDoesNotProvideSwaggerUi(): void
    {
        $container = $this->buildContainer();

        (new SwaggerUiProviderPass())->process($container);

        self::assertFalse($container->hasDefinition(SwaggerUiProviderPass::SWAGGER_UI_PROVIDER_SERVICE_ID));
        self::assertEquals(
            new Reference('api_platform.state_provider.read'),
            $this->getContentNegotiationProvider($container)->getArgument(0)
        );
    }

    private function buildContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setDefinition(
            SwaggerUiProviderPass::CONTENT_NEGOTIATION_PROVIDER_SERVICE_ID,
            (new ChildDefinition('api_platform.state_provider.content_negotiation'))
                ->replaceArgument(0, new Reference('api_platform.state_provider.read'))
        );

        return $container;
    }

    private function getContentNegotiationProvider(ContainerBuilder $container): ChildDefinition
    {
        $definition = $container->getDefinition(SwaggerUiProviderPass::CONTENT_NEGOTIATION_PROVIDER_SERVICE_ID);
        self::assertInstanceOf(ChildDefinition::class, $definition);

        return $definition;
    }
}
