<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\Rest\DependencyInjection\Security;

use Ibexa\Rest\Server\Security\RestLogoutHandler;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\FormLoginFactory;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class RestSessionBasedFactory extends FormLoginFactory
{
    public function __construct()
    {
        parent::__construct();
        unset($this->options['check_path']);

        $this->defaultSuccessHandlerOptions = [];
        $this->defaultFailureHandlerOptions = [];
    }

    /**
     * @param array<mixed> $config
     */
    protected function isRememberMeAware(array $config): bool
    {
        return false;
    }

    /**
     * @param array<mixed> $config
     */
    protected function createListener(
        ContainerBuilder $container,
        string $id,
        array $config,
        ?string $userProvider
    ): string {
        $listenerId = $this->getListenerId();
        $listener = new ChildDefinition($listenerId);
        $listener->replaceArgument(2, $id);

        /* @var \Symfony\Component\DependencyInjection\ContainerBuilder $container */
        $listenerId .= '.' . $id;
        $container->setDefinition($listenerId, $listener);
        $container->setAlias('ibexa.rest.session_authenticator', $listenerId);

        if ($container->hasDefinition('security.logout_listener.' . $id)) {
            // Copying logout handlers to REST session authenticator, to allow proper logout using it.
            $logoutListenerDef = $container->getDefinition('security.logout_listener.' . $id);
            $logoutListenerDef->addMethodCall(
                'addHandler',
                [new Reference(RestLogoutHandler::class)]
            );

            foreach ($logoutListenerDef->getMethodCalls() as $callArray) {
                if ($callArray[0] !== 'addHandler') {
                    continue;
                }

                $listener->addMethodCall('addLogoutHandler', $callArray[1]);
            }
        }

        return $listenerId;
    }

    protected function getListenerId(): string
    {
        return 'ibexa.rest.security.authentication.listener.session';
    }

    public function getPosition(): string
    {
        return 'http';
    }

    public function getKey(): string
    {
        return 'ibexa_rest_session';
    }

    /**
     * @param array<mixed> $config
     */
    protected function createEntryPoint(
        ContainerBuilder $container,
        string $id,
        array $config,
        ?string $defaultEntryPointId
    ): ?string {
        return $defaultEntryPointId;
    }

    public function createAuthenticator(
        ContainerBuilder $container,
        string $firewallName,
        array $config,
        string $userProviderId
    ): string {
        return parent::createAuthenticator($container, $firewallName . '__rest', $config, $userProviderId);
    }
}
