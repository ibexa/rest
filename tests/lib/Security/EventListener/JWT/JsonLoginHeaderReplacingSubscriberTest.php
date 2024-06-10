<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Rest\Security\EventListener\JWT;

use Ibexa\Rest\Security\EventListener\JWT\JsonLoginHeaderReplacingSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class JsonLoginHeaderReplacingSubscriberTest extends TestCase
{
    private JsonLoginHeaderReplacingSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->subscriber = new JsonLoginHeaderReplacingSubscriber();
    }

    public function testGetSubscribedEvents(): void
    {
        self::assertEquals(
            [
                KernelEvents::REQUEST => ['replaceJsonLoginHeader', 10],
            ],
            $this->subscriber->getSubscribedEvents()
        );
    }

    /**
     * @dataProvider dataProviderForTestReplacingJsonHeader
     */
    public function testReplacingJsonHeader(
        string $headerToReplace,
        string $expectedHeader,
    ): void {
        $requestEvent = $this->getRequestEventMock([
            'Content-Type' => $headerToReplace,
        ]);

        $this->subscriber->replaceJsonLoginHeader($requestEvent);

        self::assertSame(
            $expectedHeader,
            $requestEvent->getRequest()->headers->get('Content-Type')
        );
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public function dataProviderForTestReplacingJsonHeader(): iterable
    {
        yield 'replacing REST header to the required one' => [
            'application/vnd.ibexa.api.JWTInput+json',
            'application/json',
        ];

        yield 'replacing not JTW REST header does not occur' => [
            'application/vnd.ibexa.api.Content+json',
            'application/vnd.ibexa.api.Content+json',
        ];

        yield 'replacing other header does not occur' => [
            'foo_header',
            'foo_header',
        ];
    }

    /**
     * @param array<string, string> $headers
     *
     * @return \Symfony\Component\HttpKernel\Event\RequestEvent&\PHPUnit\Framework\MockObject\MockObject
     */
    private function getRequestEventMock(array $headers): RequestEvent
    {
        $request = new Request();
        $request->headers = new HeaderBag($headers);

        $requestEvent = $this->createMock(RequestEvent::class);
        $requestEvent
            ->method('getRequest')
            ->willReturn($request);

        return $requestEvent;
    }
}
