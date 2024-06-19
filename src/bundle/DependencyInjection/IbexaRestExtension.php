<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\Rest\DependencyInjection;

use Ibexa\Bundle\Core\DependencyInjection\Configuration\SiteAccessAware\ConfigurationProcessor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

use ApiPlatform\State\ApiResource\Error;
//use ApiPlatform\Symfony\Validator\Exception\ValidationException;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class IbexaRestExtension extends Extension implements PrependExtensionInterface
{
    public const EXTENSION_NAME = 'ibexa_rest';

    public function getAlias(): string
    {
        return self::EXTENSION_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $container->setParameter('api_platform.resource_class_directories', [
            '%kernel.project_dir%/src/Entity'
        ]);
        $container->setParameter('api_platform.class_name_resources', $this->getClassNameResources());

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('value_object_visitors.yml');
        $loader->load('input_parsers.yml');
        $loader->load('security.yml');
        $loader->load('default_settings.yml');

        $processor = new ConfigurationProcessor($container, 'ibexa.site_access.config');
        $processor->mapConfigArray('rest_root_resources', $config);

//        $apiPlatformXmlLoader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config/api_platform'));
//
//        $container->setAlias('api_platform.path_segment_name_generator', 'api_platform.metadata.path_segment_name_generator.underscore');
//
//        $apiPlatformXmlLoader->load('metadata/resource_name.xml');
//        $apiPlatformXmlLoader->load('metadata/property_name.xml');
//
//        $apiPlatformXmlLoader->load('metadata/xml.xml');
//        $apiPlatformXmlLoader->load('metadata/links.xml');
//        $apiPlatformXmlLoader->load('metadata/property.xml');
//        $apiPlatformXmlLoader->load('metadata/resource.xml');
//        $apiPlatformXmlLoader->load('metadata/operation.xml');
//
//        $apiPlatformXmlLoader->load('state.xml');
//        $apiPlatformXmlLoader->load('state_ibexa.xml');
////        $apiPlatformXmlLoader->load('metadata/links.xml');
////        $apiPlatformXmlLoader->load('metadata/xml.xml');
////        $apiPlatformXmlLoader->load('metadata/resource.xml');
////        $apiPlatformXmlLoader->load('metadata/resource_name.xml');
//        $apiPlatformXmlLoader->load('api.xml');
//
//
//        $apiPlatformYamlLoader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config/api_platform'));
//        $apiPlatformYamlLoader->load('api_platform.yaml');

        $apiPlatformExtension = new ApiPlatformExtension();
        $apiPlatformExtension->load($configs, $container);
    }

    private function getClassNameResources(): array
    {
        return [
            Error::class,
//            ValidationException::class,
        ];
    }

    public function prepend(ContainerBuilder $container)
    {
        if ($container->hasExtension('nelmio_cors')) {
            $file = __DIR__ . '/../Resources/config/nelmio_cors.yml';
            $config = Yaml::parse(file_get_contents($file));
            $container->prependExtensionConfig('nelmio_cors', $config);
            $container->addResource(new FileResource($file));
        }

        $this->prependRouterConfiguration($container);
        $this->prependJMSTranslation($container);
    }

    private function prependRouterConfiguration(ContainerBuilder $container)
    {
        $config = ['router' => ['default_router' => ['non_siteaccess_aware_routes' => ['ibexa.rest.']]]];
        $container->prependExtensionConfig('ibexa', $config);
    }

    private function prependJMSTranslation(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('jms_translation', [
            'configs' => [
                'ibexa_rest' => [
                    'dirs' => [
                        __DIR__ . '/../../',
                    ],
                    'excluded_dirs' => ['Behat', 'Tests', 'node_modules', 'Features'],
                    'output_dir' => __DIR__ . '/../Resources/translations/',
                    'output_format' => 'xliff',
                ],
            ],
        ]);
    }
}

class_alias(IbexaRestExtension::class, 'EzSystems\EzPlatformRestBundle\DependencyInjection\EzPlatformRestExtension');
