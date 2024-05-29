<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\Rest\EventListener;

use Ibexa\Bundle\Rest\EventListener\CsrfListener;
use Ibexa\Contracts\Rest\Exceptions\UnauthorizedException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class CsrfListenerTest extends EventListenerTest
{
    public const string VALID_TOKEN = 'valid';
    public const string INVALID_TOKEN = 'invalid';
    public const string INTENTION = 'rest';

    protected EventDispatcherInterface $eventDispatcherMock;

    /**
     * If set to null before initializing mocks, Request::getSession() is expected not to be called.
     */
    protected $sessionMock;

    protected bool $sessionIsStarted = true;

    protected $csrfTokenHeaderValue = self::VALID_TOKEN;

    /**
     * Route returned by Request::get( '_route' )
     * If set to false, get( '_route' ) is expected not to be called.
     *
     * @var string
     */
    protected $route = 'ibexa.rest.something';

    /**
     * If set to false, Request::getRequestMethod() is expected not to be called.
     */
    protected $requestMethod = 'POST';

    public function provideExpectedSubscribedEventTypes(): array
    {
        return [
            [[KernelEvents::REQUEST]],
        ];
    }

    public function testIsNotRestRequest(): void
    {
        $this->isRestRequest = false;

        $this->requestMethod = false;
        $this->sessionMock = false;
        $this->route = false;
        $this->csrfTokenHeaderValue = null;

        $listener = $this->getEventListener();
        $listener->onKernelRequest($this->getEvent());
    }

    public function testCsrfDisabled()
    {
        $this->requestMethod = false;
        $this->sessionMock = false;
        $this->route = false;
        $this->csrfTokenHeaderValue = null;

        $this->getEventListener(false)->onKernelRequest($this->getEvent());
    }

    public function testNoSessionStarted()
    {
        $this->sessionIsStarted = false;

        $this->requestMethod = false;
        $this->route = false;
        $this->csrfTokenHeaderValue = null;

        $this->getEventListener()->onKernelRequest($this->getEvent());
    }

    /**
     * Tests that method CSRF check don't apply to are indeed ignored.
     *
     * @dataProvider getIgnoredRequestMethods
     */
    public function testIgnoredRequestMethods(string $ignoredMethod): void
    {
        $this->requestMethod = $ignoredMethod;
        $this->route = false;
        $this->csrfTokenHeaderValue = null;

        $this->getEventListener()->onKernelRequest($this->getEvent());
    }

    /**
     * @return array<array<string>>
     */
    public function getIgnoredRequestMethods(): array
    {
        return [
            ['GET'],
            ['HEAD'],
            ['OPTIONS'],
        ];
    }

    /**
     * @dataProvider provideSessionRoutes
     */
    public function testSessionRequests($route): void
    {
        $this->route = $route;
        $this->csrfTokenHeaderValue = null;

        $this->getEventListener()->onKernelRequest($this->getEvent());
    }

    /**
     * @return array<array<string>>
     */
    public static function provideSessionRoutes(): array
    {
        return [
            ['ibexa.rest.create_session'],
            ['ibexa.rest.check_session'],
            ['ibexa.rest.delete_session'],
        ];
    }

    public function testSkipCsrfProtection(): void
    {
        $this->enableCsrfProtection = false;
        $this->csrfTokenHeaderValue = null;

        $listener = $this->getEventListener();
        $listener->onKernelRequest($this->getEvent());
    }

    public function testNoHeader(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->csrfTokenHeaderValue = false;

        $this->getEventListener()->onKernelRequest($this->getEvent());
    }

    public function testInvalidToken(): void
    {
        $this->expectException(UnauthorizedException::class);

        $this->csrfTokenHeaderValue = self::INVALID_TOKEN;

        $this->getEventListener()->onKernelRequest($this->getEvent());
    }

    public function testValidToken(): void
    {
        $this->getEventDispatcherMock()
            ->expects(self::once())
            ->method('dispatch');

        $this->getEventListener()->onKernelRequest($this->getEvent());
    }

    /**
     * @return \Symfony\Component\Security\Csrf\CsrfTokenManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getCsrfProviderMock(): CsrfTokenManagerInterface
    {
        $provider = $this->createMock(CsrfTokenManagerInterface::class);
        $provider->expects(self::any())
            ->method('isTokenValid')
            ->willReturnCallback(
                static function (CsrfToken $token): bool {
                    if ($token == new CsrfToken(self::INTENTION, self::VALID_TOKEN)) {
                        return true;
                    }

                    return false;
                }
            );

        return $provider;
    }

    protected function getEvent(): RequestEvent
    {
        $event = $this->createMock(RequestEvent::class);

        $event
            ->expects(self::any())
            ->method('getRequestType')
            ->willReturn($this->requestType);

        return $event;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Session\SessionInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getSessionMock(): SessionInterface
    {
        if (!isset($this->sessionMock)) {
            $this->sessionMock = $this->createMock(SessionInterface::class);
            $this->sessionMock
                ->expects(self::atLeastOnce())
                ->method('isStarted')
                ->willReturn($this->sessionIsStarted);
        }

        return $this->sessionMock;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\ParameterBag|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getRequestHeadersMock(): ParameterBag
    {
        if (!isset($this->requestHeadersMock)) {
            $this->requestHeadersMock = parent::getRequestHeadersMock();

            if ($this->csrfTokenHeaderValue === null) {
                $this->requestHeadersMock
                    ->expects(self::never())
                    ->method('has');

                $this->requestHeadersMock
                    ->expects(self::never())
                    ->method('get');
            } else {
                $this->requestHeadersMock
                    ->expects(self::atLeastOnce())
                    ->method('has')
                    ->with(CsrfListener::CSRF_TOKEN_HEADER)
                    ->willReturn(true);

                $this->requestHeadersMock
                    ->expects(self::atLeastOnce())
                    ->method('get')
                    ->with(CsrfListener::CSRF_TOKEN_HEADER)
                    ->willReturn($this->csrfTokenHeaderValue);
            }
        }

        return $this->requestHeadersMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\HttpFoundation\Request
     */
    protected function getRequestMock(): Request
    {
        if (!isset($this->requestMock)) {
            $this->requestMock = parent::getRequestMock();

            if ($this->sessionMock === false) {
                $this->requestMock
                    ->expects(self::never())
                    ->method('getSession');
            } else {
                $this->requestMock
                    ->expects(self::atLeastOnce())
                    ->method('getSession')
                    ->willReturn($this->getSessionMock());
            }

            if ($this->route === false) {
                $this->requestMock
                    ->expects(self::never())
                    ->method('get');
            } else {
                $this->requestMock
                    ->expects(self::atLeastOnce())
                    ->method('get')
                    ->with('_route')
                    ->willReturn($this->route);
            }
        }

        return $this->requestMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected function getEventDispatcherMock(): EventDispatcherInterface
    {
        if (!isset($this->eventDispatcherMock)) {
            $this->eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
        }

        return $this->eventDispatcherMock;
    }

    protected function getEventListener(?bool $csrfEnabled = true): EventSubscriberInterface
    {
        if ($csrfEnabled) {
            return new CsrfListener(
                $this->getEventDispatcherMock(),
                $csrfEnabled,
                self::INTENTION,
                $this->getCsrfProviderMock()
            );
        }

        return new CsrfListener(
            $this->getEventDispatcherMock(),
            $csrfEnabled,
            self::INTENTION
        );
    }
}
