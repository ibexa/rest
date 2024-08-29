<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\Rest\EventListener;

use Ibexa\Bundle\Rest\Exception\UnexpectedUserException;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;

final class UserCheckRequestListener implements EventSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private PermissionResolver $permissionResolver;

    private Security $security;

    public function __construct(PermissionResolver $permissionResolver, Security $security)
    {
        $this->permissionResolver = $permissionResolver;
        $this->security = $security;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['checkUser'],
            ],
        ];
    }

    public function checkUser(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (!$event->getRequest()->attributes->get('is_rest_request')) {
            return;
        }

        $request = $event->getRequest();
        $expectedUserIdentifier = $request->headers->get('X-Expected-User');
        if (empty($expectedUserIdentifier)) {
            return;
        }

        $user = $this->security->getUser();
        if ($user === null || $expectedUserIdentifier !== $user->getUserIdentifier()) {
            throw new UnexpectedUserException('Expectation failed. User changed.', Response::HTTP_UNAUTHORIZED);
        }
    }
}
