<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Security\EventListener\JWT;

use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Core\MVC\Symfony\Security\UserInterface as IbexaUser;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class AuthenticationSuccessSubscriber implements EventSubscriberInterface
{
    public function __construct(private PermissionResolver $permissionResolver)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::AUTHENTICATION_SUCCESS => ['onAuthenticationSuccess', 10],
        ];
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getUser();
        if ($user instanceof IbexaUser) {
            $this->permissionResolver->setCurrentUserReference($user->getAPIUser());
        }

        $this->normalizeResponseToRest($event);
    }

    /*
     * This method provides BC compatibility for the JWT Token REST response
     * since the new Lexik/JWT json_login authenticator changes its form.
     *
     * @TODO: Drop on releasing the new REST API version.
     */
    private function normalizeResponseToRest(AuthenticationSuccessEvent $event): void
    {
        $token = $event->getData()['token'];

        $event->setData([
            'JWT' => [
                '_media-type' => 'application/vnd.ibexa.api.JWT+json',
                '_token' => $token,
                'token' => $token,
            ],
        ]);
    }
}