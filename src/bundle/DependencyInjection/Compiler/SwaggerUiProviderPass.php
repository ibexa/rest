<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\Rest\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Puts the Swagger UI provider, fed with the Ibexa OpenAPI document, in front of the content
 * negotiation provider. API Platform registers its Swagger UI provider only when Swagger UI
 * or ReDoc is enabled (since API Platform 4.2 `enable_docs: false` disables both).
 */
final readonly class SwaggerUiProviderPass implements CompilerPassInterface
{
    public const string SWAGGER_UI_PROVIDER_SERVICE_ID = 'ibexa.api_platform.swagger_ui.provider';

    public const string CONTENT_NEGOTIATION_PROVIDER_SERVICE_ID = 'ibexa.api_platform.state_provider.content_negotiation';

    private const string API_PLATFORM_SWAGGER_UI_PROVIDER_SERVICE_ID = 'api_platform.swagger_ui.provider';

    private const string OPENAPI_FACTORY_SERVICE_ID = 'ibexa.api_platform.ibexa_openapi.factory';

    public function process(ContainerBuilder $container): void
    {
        if (
            !$container->hasDefinition(self::API_PLATFORM_SWAGGER_UI_PROVIDER_SERVICE_ID)
            || !$container->hasDefinition(self::CONTENT_NEGOTIATION_PROVIDER_SERVICE_ID)
        ) {
            return;
        }

        $container->setDefinition(
            self::SWAGGER_UI_PROVIDER_SERVICE_ID,
            (new ChildDefinition(self::API_PLATFORM_SWAGGER_UI_PROVIDER_SERVICE_ID))
                ->replaceArgument(1, new Reference(self::OPENAPI_FACTORY_SERVICE_ID))
        );

        $container
            ->getDefinition(self::CONTENT_NEGOTIATION_PROVIDER_SERVICE_ID)
            ->replaceArgument(0, new Reference(self::SWAGGER_UI_PROVIDER_SERVICE_ID));
    }
}
