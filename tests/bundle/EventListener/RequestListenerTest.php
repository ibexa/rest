<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\Bundle\Rest\EventListener;

use Ibexa\Bundle\Rest\EventListener\RequestListener;
use Ibexa\Contracts\Rest\UriParser\UriParserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class RequestListenerTest extends EventListenerTest
{
    public const REST_ROUTE = '/api/ibexa/v2/rest-route';
    public const NON_REST_ROUTE = '/non-rest-route';

    /**
     * @return array<array{array{string}}>
     */
    public function provideExpectedSubscribedEventTypes(): array
    {
        return [
            [
                [KernelEvents::REQUEST],
            ],
        ];
    }

    /**
     * @return iterable<string, array{string, bool}>
     */
    public static function getDataForTestOnKernelRequest(): iterable
    {
        yield 'REST request' => [self::REST_ROUTE, true];
        yield 'non-REST request' => [self::NON_REST_ROUTE, false];
    }

    public function testOnKernelRequestNotMasterRequest(): void
    {
        $request = $this->performFakeRequest(self::REST_ROUTE, HttpKernelInterface::SUB_REQUEST);

        self::assertTrue($request->attributes->get('is_rest_request'));
    }

    /**
     * @dataProvider getDataForTestOnKernelRequest
     */
    public function testOnKernelRequest(string $uri, bool $isExpectedRestRequest): void
    {
        $request = $this->performFakeRequest($uri);

        self::assertSame($isExpectedRestRequest, $request->attributes->get('is_rest_request'));
    }

    protected function getEventListener(): RequestListener
    {
        // the decision itself is covered by \Ibexa\Tests\Bundle\Rest\UriParser\UriParserTest
        $uriParser = $this->createStub(UriParserInterface::class);
        $uriParser
            ->method('isRestRequest')
            ->willReturnCallback(
                static fn (Request $request): bool => $request->getPathInfo() === self::REST_ROUTE
            );

        return new RequestListener($uriParser);
    }

    protected function performFakeRequest(string $uri, int $type = HttpKernelInterface::MAIN_REQUEST): Request
    {
        $event = new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            Request::create($uri),
            $type
        );

        $this->getEventListener()->onKernelRequest($event);

        return $event->getRequest();
    }
}

class_alias(RequestListenerTest::class, 'EzSystems\EzPlatformRestBundle\Tests\EventListener\RequestListenerTest');
