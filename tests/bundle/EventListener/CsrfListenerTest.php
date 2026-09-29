<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\Rest\EventListener;

use Ibexa\Bundle\Rest\EventListener\CsrfListener;
use Ibexa\Contracts\Rest\Exceptions\UnauthorizedException;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class CsrfListenerTest extends EventListenerTestCase
{
    public const string VALID_TOKEN = 'valid';
    public const string INVALID_TOKEN = 'invalid';
    public const string INTENTION = 'rest';

    public static function provideExpectedSubscribedEventTypes(): array
    {
        return [
            [[KernelEvents::REQUEST]],
        ];
    }

    public function testIsNotRestRequest(): void
    {
        $listener = $this->getEventListener();
        $request = new Request();

        $listener->onKernelRequest(
            $this->getEvent($request)
        );
    }

    public function testCsrfDisabled(): void
    {
        $request = new Request(attributes: [
            'is_rest_request' => true,
        ]);

        $this
            ->getEventListener(false)
            ->onKernelRequest($this->getEvent($request));
    }

    public function testNoSessionStarted(): void
    {
        $request = new Request(attributes: [
            'is_rest_request' => true,
        ]);
        $request->setSession($this->getSessionMock(false));

        $this
            ->getEventListener()
            ->onKernelRequest($this->getEvent($request));
    }

    /**
     * Tests that method CSRF check don't apply to are indeed ignored.
     */
    #[DataProvider('getIgnoredRequestMethods')]
    public function testIgnoredRequestMethods(string $ignoredMethod): void
    {
        $request = new Request(attributes: [
            'is_rest_request' => true,
        ]);
        $request->setSession($this->getSessionMock());
        $request->setMethod($ignoredMethod);

        $this
            ->getEventListener()
            ->onKernelRequest($this->getEvent($request));
    }

    /**
     * @return array<array<string>>
     */
    public static function getIgnoredRequestMethods(): array
    {
        return [
            ['GET'],
            ['HEAD'],
            ['OPTIONS'],
        ];
    }

    public function testSessionRequests(): void
    {
        $request = new Request(attributes: [
            'is_rest_request' => true,
        ]);
        $request->setSession($this->getSessionMock());
        $request->setMethod('GET');

        $this
            ->getEventListener()
            ->onKernelRequest($this->getEvent($request));
    }

    public function testSkipCsrfProtection(): void
    {
        $request = new Request(attributes: [
            'is_rest_request' => true,
        ]);

        $this
            ->getEventListener(false)
            ->onKernelRequest($this->getEvent($request));
    }

    public function testNoHeader(): void
    {
        $request = new Request(attributes: [
            'is_rest_request' => true,
        ]);
        $request->setSession($this->getSessionMock());
        $request->setMethod('POST');

        $this->expectException(UnauthorizedException::class);

        $this
            ->getEventListener()
            ->onKernelRequest($this->getEvent($request));
    }

    public function testInvalidToken(): void
    {
        $request = new Request(
            attributes: ['is_rest_request' => true],
            server: ['HTTP_X_CSRF_TOKEN' => self::INVALID_TOKEN],
        );
        $request->setSession($this->getSessionMock());
        $request->setMethod('POST');

        $this->expectException(UnauthorizedException::class);

        $this
            ->getEventListener()
            ->onKernelRequest($this->getEvent($request));
    }

    public function testValidToken(): void
    {
        $request = new Request(
            attributes: ['is_rest_request' => true],
            server: ['HTTP_X_CSRF_TOKEN' => self::VALID_TOKEN],
        );
        $request->setSession($this->getSessionMock());
        $request->setMethod('POST');

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
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private function getEventDispatcherMock(): EventDispatcherInterface
    {
        return $this->createMock(EventDispatcherInterface::class);
    }
}
