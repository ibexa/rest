<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\Rest;

use ApiPlatform\Symfony\Bundle\ApiPlatformBundle;
use Hautelook\TemplatedUriBundle\HautelookTemplatedUriBundle;
use Ibexa\Bundle\Rest\IbexaRestBundle;
use Ibexa\Contracts\Rest\UriParser\UriParserInterface;
use Ibexa\Contracts\Test\Core\IbexaTestKernel as CoreIbexaTestKernel;
use Ibexa\Rest\Server\Controller\Root as RestRootController;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Serializer\Serializer;

final class IbexaTestKernel extends CoreIbexaTestKernel
{
    public function registerBundles(): iterable
    {
        yield from parent::registerBundles();

        yield new HautelookTemplatedUriBundle();
        yield new IbexaRestBundle();
        yield new ApiPlatformBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        parent::registerContainerConfiguration($loader);

        $loader->load(static function (ContainerBuilder $container): void {
            self::addSyntheticService($container, JWTTokenManagerInterface::class);

            self::loadRouting($container);
        });
    }

    protected static function getExposedServicesByClass(): iterable
    {
        yield from parent::getExposedServicesByClass();
        yield RestRootController::class;
        yield UriParserInterface::class;
    }

    protected static function getExposedServicesById(): iterable
    {
        yield from parent::getExposedServicesById();
        yield 'ibexa.rest.serializer' => Serializer::class;
    }

    private static function loadRouting(ContainerBuilder $container): void
    {
        $container->loadFromExtension('framework', [
            'router' => [
                'resource' => __DIR__ . '/Resources/test_routing.yaml',
            ],
        ]);
    }

    protected function loadServices(LoaderInterface $loader): void
    {
        parent::loadServices($loader);

        $loader->load(__DIR__ . '/Resources/services.yaml');
    }
}
