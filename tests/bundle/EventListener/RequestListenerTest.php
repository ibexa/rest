<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Bundle\Rest\EventListener;

use Ibexa\Bundle\Rest\EventListener\RequestListener;
use Ibexa\Bundle\Rest\UriParser\UriParser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

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
     * @retirm array<array{string, bool}>
     */
    public static function getDataForTestOnKernelRequest(): array
    {
        return [
            // REST requests
            [self::REST_ROUTE, true],
            ['/api/ibexa/v2/true', true],
            ['/api/bundle-name/v2/true', true],
            ['/api/MyBundle12/v2/true', true],
            ['/api/ThisIs_Bundle123/v2/true', true],
            ['/api/my-bundle/v1/true', true],
            ['/api/my-bundle/v2/true', true],
            ['/api/my-bundle/v2.7/true', true],
            ['/api/my-bundle/v122.73/true', true],
            // non-REST requests
            [self::NON_REST_ROUTE, false],
            ['/ap/ezp/v2/false', false],
            ['/api/bundle name/v2/false', false],
            ['/api/My/Bundle/v2/false', false],
            ['/api//v2/false', false],
            ['/api/my-bundle/v/false', false],
            ['/api/my-bundle/v2-2/false', false],
            ['/api/my-bundle/v2 7/false', false],
            ['/api/my-bundle/v/7/false', false],
        ];
    }

    /**
     * @return array<array{string}>
     */
    public static function nonRestRequestsUrisProvider(): array
    {
        return [
        ];
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
        return new RequestListener(
            new UriParser($this->createMock(UrlMatcherInterface::class))
        );
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
