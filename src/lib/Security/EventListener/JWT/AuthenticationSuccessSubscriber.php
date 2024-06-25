<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Security\EventListener\JWT;

use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Core\MVC\Symfony\Security\UserInterface as IbexaUser;
use Ibexa\Rest\Server\Exceptions\BadResponseException;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class AuthenticationSuccessSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private PermissionResolver $permissionResolver,
        private RequestStack $requestStack,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::AUTHENTICATION_SUCCESS => ['onAuthenticationSuccess', 10],
        ];
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return;
        }

        if (!$request->attributes->get('is_rest_request')) {
            return;
        }

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
     * @deprecated 5.0.0. Will be removed in the next REST API version.
     */
    /**
     * @throws \Ibexa\Rest\Server\Exceptions\BadResponseException
     */
    private function normalizeResponseToRest(AuthenticationSuccessEvent $event): void
    {
        $eventData = $event->getData();
        if (!isset($eventData['token'])) {
            throw new BadResponseException('JWT Token has not been generated.');
        }

        $token = $eventData['token'];
        $event->setData([
            'JWT' => [
                '_media-type' => 'application/vnd.ibexa.api.JWT+json',
                '_token' => $token,
                'token' => $token,
            ],
        ]);
    }
}
