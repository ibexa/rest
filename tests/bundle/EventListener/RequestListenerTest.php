<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\Rest\EventListener;

use Ibexa\Bundle\Rest\EventListener\RequestListener;
use Ibexa\Bundle\Rest\UriParser\UriParser;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

final class RequestListenerTest extends EventListenerTestCase
{
    public const string REST_ROUTE = '/api/ibexa/v2/rest-route';
    public const string NON_REST_ROUTE = '/non-rest-route';

    /**
     * @return array<array{array{string}}>
     */
    public static function provideExpectedSubscribedEventTypes(): array
    {
        return [
            [
                [KernelEvents::REQUEST],
            ],
        ];
    }

    /**
     * @return array<array{string, bool}>
     */
    public static function getDataForTestOnKernelRequest(): array
    {
        return [
            // REST requests
            [self::REST_ROUTE, true],
            ['/api/ibexa/v2/', true],
            ['/api/ibexa/v2/content/objects/1', true],
            // non-REST requests
            ['/api/ibexa/v3/translations', false],
            ['/api/ibexa/ping', false],
            ['/api/ibexa/errors/404', false],
            ['/api/errors/404', false],
            ['/api/ibexa/v2', false],
            ['/api/ibexa/v2.7/x', false],
            ['/api/my-bundle/v2/x', false],
            ['/api/ibexav2/x', false],
            [self::NON_REST_ROUTE, false],
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

    #[DataProvider('getDataForTestOnKernelRequest')]
    public function testOnKernelRequest(
        string $uri,
        bool $isExpectedRestRequest
    ): void {
        $request = $this->performFakeRequest($uri);

        self::assertSame($isExpectedRestRequest, $request->attributes->get('is_rest_request'));
    }

    protected function getEventListener(?bool $csrfEnabled = null): RequestListener
    {
        return new RequestListener(
            new UriParser(self::createStub(UrlMatcherInterface::class))
        );
    }

    protected function performFakeRequest(
        string $uri,
        int $type = HttpKernelInterface::MAIN_REQUEST
    ): Request {
        $event = new RequestEvent(
            self::createStub(HttpKernelInterface::class),
            Request::create($uri),
            $type
        );

        $this->getEventListener()->onKernelRequest($event);

        return $event->getRequest();
    }
}
