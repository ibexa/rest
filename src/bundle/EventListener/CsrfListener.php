<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\Rest\EventListener;

use Ibexa\Bundle\Rest\RestEvents;
use Ibexa\Contracts\Rest\Exceptions\UnauthorizedException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class CsrfListener implements EventSubscriberInterface
{
    /**
     * Name of the HTTP header containing CSRF token.
     */
    public const string CSRF_TOKEN_HEADER = 'X-CSRF-Token';

    private ?CsrfTokenManagerInterface $csrfTokenManager;

    private EventDispatcherInterface $eventDispatcher;

    private bool $csrfEnabled;

    private string $csrfTokenIntention;

    /**
     * Note that CSRF provider needs to be optional as it will not be available
     * when CSRF protection is disabled.
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        bool $csrfEnabled,
        string $csrfTokenIntention,
        ?CsrfTokenManagerInterface $csrfTokenManager = null
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->csrfEnabled = $csrfEnabled;
        $this->csrfTokenIntention = $csrfTokenIntention;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    /**
     * This method validates CSRF token if CSRF protection is enabled.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$request->attributes->get('is_rest_request')) {
            return;
        }

        if (!$this->csrfEnabled) {
            return;
        }

        // skip CSRF validation if no session is running
        if (!$request->getSession()->isStarted()) {
            return;
        }

        if ($this->isMethodSafe($request->getMethod())) {
            return;
        }

        if (!$request->attributes->getBoolean('csrf_protection', true)) {
            return;
        }

        if (!$this->checkCsrfToken($request)) {
            throw new UnauthorizedException('Missing or invalid CSRF token');
        }

        // Dispatching event so that CSRF token intention can be injected into Legacy Stack
        $this->eventDispatcher->dispatch($event, RestEvents::REST_CSRF_TOKEN_VALIDATED);
    }

    private function isMethodSafe(string $method): bool
    {
        return in_array($method, ['GET', 'HEAD', 'OPTIONS']);
    }

    private function checkCsrfToken(Request $request): bool
    {
        if (!$request->headers->has(self::CSRF_TOKEN_HEADER)) {
            return false;
        }

        if ($this->csrfTokenManager === null) {
            return false;
        }

        return $this->csrfTokenManager->isTokenValid(
            new CsrfToken(
                $this->csrfTokenIntention,
                $request->headers->get(self::CSRF_TOKEN_HEADER)
            )
        );
    }
}
