<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Rest\Security\EventListener\JWT;

use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Core\MVC\Symfony\Security\User;
use Ibexa\Core\Repository\Values\User\User as ApiUser;
use Ibexa\Rest\Security\EventListener\JWT\AuthenticationSuccessSubscriber;
use Ibexa\Rest\Server\Exceptions\BadResponseException;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Core\User\UserInterface;

final class AuthenticationSuccessSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $subscriber = new AuthenticationSuccessSubscriber(
            $this->createMock(PermissionResolver::class),
            $this->getRequestStackMock()
        );

        self::assertEquals(
            [
                Events::AUTHENTICATION_SUCCESS => ['onAuthenticationSuccess', 10],
            ],
            $subscriber->getSubscribedEvents()
        );
    }

    /**
     * @dataProvider dataProviderForTestOnAuthenticationSuccess
     */
    public function testOnAuthenticationSuccess(
        UserInterface $user,
        bool $isPermissionResolverInvoked
    ): void {
        $permissionResolver = $this->createMock(PermissionResolver::class);
        $permissionResolver
            ->expects($isPermissionResolverInvoked === true ? self::once() : self::never())
            ->method('setCurrentUserReference');

        $event = new AuthenticationSuccessEvent(['token' => 'foo_token'], $user, new Response());

        $subscriber = new AuthenticationSuccessSubscriber(
            $permissionResolver,
            $this->getRequestStackMock()
        );

        $subscriber->onAuthenticationSuccess($event);

        self::assertSame(
            [
                'JWT' => [
                    '_media-type' => 'application/vnd.ibexa.api.JWT+json',
                    '_token' => 'foo_token',
                    'token' => 'foo_token',
                ],
            ],
            $event->getData()
        );
    }

    /**
     * @return iterable<string, array{\Symfony\Component\Security\Core\User\UserInterface, bool}>
     */
    public function dataProviderForTestOnAuthenticationSuccess(): iterable
    {
        yield 'authorizing Ibexa user' => [
            new User($this->createMock(ApiUser::class)),
            true,
        ];

        yield 'authorizing non-Ibexa user' => [
            new InMemoryUser('foo', 'bar'),
            false,
        ];
    }

    public function testResponseIsMissingJwtToken(): void
    {
        $subscriber = new AuthenticationSuccessSubscriber(
            $this->createMock(PermissionResolver::class),
            $this->getRequestStackMock()
        );

        $event = new AuthenticationSuccessEvent(
            [
                'some' => 'data',
                'some_other' => 'data',
                'but_no_token' => 'anywhere',
            ],
            new User($this->createMock(ApiUser::class)),
            new Response()
        );

        $this->expectException(BadResponseException::class);

        $subscriber->onAuthenticationSuccess($event);
    }

    public function testSkippingResponseNormalizingForNonRestRequest(): void
    {
        $subscriber = new AuthenticationSuccessSubscriber(
            $this->createMock(PermissionResolver::class),
            $this->getRequestStackMock(false)
        );

        $event = new AuthenticationSuccessEvent(
            [
                'token' => 'foo',
            ],
            new User($this->createMock(ApiUser::class)),
            new Response()
        );

        $subscriber->onAuthenticationSuccess($event);

        self::assertSame(
            [
                'token' => 'foo',
            ],
            $event->getData()
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RequestStack&\PHPUnit\Framework\MockObject\MockObject
     */
    private function getRequestStackMock(bool $isRestRequest = true): RequestStack
    {
        $request = new Request(
            [],
            [],
            $isRestRequest === true ? ['is_rest_request' => true] : []
        );

        $requestStackMock = $this->createMock(RequestStack::class);
        $requestStackMock
            ->method('getCurrentRequest')
            ->willReturn($request);

        return $requestStackMock;
    }
}
