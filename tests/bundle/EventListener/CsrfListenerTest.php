<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\Rest\EventListener;

use Ibexa\Bundle\Rest\EventListener\CsrfListener;
use Ibexa\Core\Base\Exceptions\UnauthorizedException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\HeaderBag;
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

    public function provideExpectedSubscribedEventTypes(): array
    {
        return [
            [[KernelEvents::REQUEST]],
        ];
    }

    public function testIsNotRestRequest(): void
    {
        $listener = $this->getEventListener();
        $request = $this->createMock(Request::class);
        $request->attributes = new ParameterBag();

        $listener->onKernelRequest(
            $this->getEvent($request)
        );
    }

    public function testCsrfDisabled(): void
    {
        $request = $this->createMock(Request::class);
        $request->attributes = new ParameterBag([
            'is_rest_request' => true,
        ]);

        $this
            ->getEventListener(false)
            ->onKernelRequest($this->getEvent($request));
    }

    public function testNoSessionStarted(): void
    {
        $request = $this->createMock(Request::class);
        $request->attributes = new ParameterBag([
            'is_rest_request' => true,
        ]);

        $request
            ->method('getSession')
            ->willReturn($this->getSessionMock(false));

        $this
            ->getEventListener()
            ->onKernelRequest($this->getEvent($request));
    }

    /**
     * Tests that method CSRF check don't apply to are indeed ignored.
     *
     * @dataProvider getIgnoredRequestMethods
     */
    public function testIgnoredRequestMethods(string $ignoredMethod): void
    {
        $request = $this->createMock(Request::class);
        $request->attributes = new ParameterBag([
            'is_rest_request' => true,
        ]);

        $request
            ->method('getSession')
            ->willReturn($this->getSessionMock());

        $request
            ->method('getMethod')
            ->willReturn($ignoredMethod);

        $this
            ->getEventListener()
            ->onKernelRequest($this->getEvent($request));
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

    public function testSessionRequests(): void
    {
        $request = $this->createMock(Request::class);
        $request->attributes = $this->getRequestAttributesMock();
        $request->headers = $this->getRequestHeadersMock();

        $request
            ->method('getSession')
            ->willReturn($this->getSessionMock());

        $request
            ->method('getMethod')
            ->willReturn('GET');

        $this
            ->getEventListener()
            ->onKernelRequest($this->getEvent($request));
    }

    public function testSkipCsrfProtection(): void
    {
        $request = $this->createMock(Request::class);
        $request->attributes = $this->getRequestAttributesMock();
        $request->headers = $this->getRequestHeadersMock();

        $this
            ->getEventListener(false)
            ->onKernelRequest($this->getEvent($request));
    }

    public function testNoHeader(): void
    {
        $request = $this->createMock(Request::class);
        $request->attributes = $this->getRequestAttributesMock();
        $request->headers = $this->getRequestHeadersMock();

        $request
            ->method('getMethod')
            ->willReturn('POST');

        $request
            ->method('getSession')
            ->willReturn($this->getSessionMock());

        $this->expectException(UnauthorizedException::class);

        $this
            ->getEventListener()
            ->onKernelRequest($this->getEvent($request));
    }

    public function testInvalidToken(): void
    {
        $request = $this->createMock(Request::class);
        $request->attributes = $this->getRequestAttributesMock();
        $request->headers = $this->getRequestHeadersMock(self::INVALID_TOKEN);

        $request
            ->method('getMethod')
            ->willReturn('POST');

        $request
            ->method('getSession')
            ->willReturn($this->getSessionMock());

        $this->expectException(UnauthorizedException::class);

        $this
            ->getEventListener()
            ->onKernelRequest($this->getEvent($request));
    }

    public function testValidToken(): void
    {
        $request = $this->createMock(Request::class);
        $request->attributes = $this->getRequestAttributesMock();
        $request->headers = $this->getRequestHeadersMock(self::VALID_TOKEN);

        $request
            ->method('getMethod')
            ->willReturn('POST');

        $request
            ->method('getSession')
            ->willReturn($this->getSessionMock());

        $this
            ->getEventListener(true, $this->getEventDispatcherMock())
            ->onKernelRequest($this->getEvent($request));
    }

    protected function getEventListener(
        ?bool $csrfEnabled = true,
        ?EventDispatcherInterface $eventDispatcher = null
    ): CsrfListener {
        return new CsrfListener(
            $eventDispatcher ?? $this->getEventDispatcherMock(),
            $csrfEnabled ?? true,
            self::INTENTION,
            $csrfEnabled === true ? $this->getCsrfProviderMock() : null
        );
    }

    private function getEvent(Request $request): RequestEvent
    {
        $event = $this->createMock(RequestEvent::class);
        $event
            ->expects(self::once())
            ->method('getRequest')
            ->willReturn($request);

        return $event;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Session\SessionInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getSessionMock(bool $isSessionStarted = true): SessionInterface
    {
        $sessionMock = $this->createMock(SessionInterface::class);

        $sessionMock
            ->expects(self::atLeastOnce())
            ->method('isStarted')
            ->willReturn($isSessionStarted);

        return $sessionMock;
    }

    /**
     * @return \Symfony\Component\Security\Csrf\CsrfTokenManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getCsrfProviderMock(): CsrfTokenManagerInterface
    {
        $provider = $this->createMock(CsrfTokenManagerInterface::class);
        $provider->expects(self::any())
            ->method('isTokenValid')
            ->willReturnCallback(
                static function (CsrfToken $token): bool {
                    return
                        $token->getId() === self::INTENTION &&
                        $token->getValue() === self::VALID_TOKEN;
                }
            );

        return $provider;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\HeaderBag&\PHPUnit\Framework\MockObject\MockObject
     */
    private function getRequestHeadersMock(?string $csrfTokenHeaderValue = null): HeaderBag
    {
        $headerBag = $this->createMock(HeaderBag::class);

        if ($csrfTokenHeaderValue === null) {
            $headerBag
                ->expects(self::never())
                ->method('get');
        } else {
            $headerBag
                ->expects(self::once())
                ->method('has')
                ->with(CsrfListener::CSRF_TOKEN_HEADER)
                ->willReturn($csrfTokenHeaderValue !== null);

            $headerBag
                ->expects(self::once())
                ->method('get')
                ->with(CsrfListener::CSRF_TOKEN_HEADER)
                ->willReturn($csrfTokenHeaderValue);
        }

        return $headerBag;
    }

    /**
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private function getEventDispatcherMock(): EventDispatcherInterface
    {
        return $this->createMock(EventDispatcherInterface::class);
    }
}
