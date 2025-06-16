<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\Rest\EventListener;

use Ibexa\Bundle\Rest\EventListener\SupportedMediaTypesSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class SupportedMediaTypesSubscriberTest extends TestCase
{
    /** @var \Symfony\Component\HttpKernel\HttpKernelInterface&\PHPUnit\Framework\MockObject\MockObject */
    private HttpKernelInterface $kernel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kernel = $this->createMock(HttpKernelInterface::class);
    }

    public function testDoesNothingWhenSupportedMediaTypesParameterIsNotSet(): void
    {
        $request = new Request();
        $event = new RequestEvent($this->kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber = new SupportedMediaTypesSubscriber();
        $subscriber->allowOnlySupportedMediaTypes($event);

        self::expectNotToPerformAssertions();
    }

    public function testDoesNothingWhenSupportedMediaTypesParameterIsEmpty(): void
    {
        $request = new Request();
        $request->attributes->set('supported_media_types', []);

        $subscriber = new SupportedMediaTypesSubscriber();
        $event = new RequestEvent($this->kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber->allowOnlySupportedMediaTypes($event);

        self::expectNotToPerformAssertions();
    }

    public function testDoesNothingWhenMediaTypeIsSupported(): void
    {
        $request = new Request();
        $request->attributes->set('supported_media_types', ['json', 'xml']);
        $request->headers = new HeaderBag([
            'Content-Type' => 'application/vnd.ibexa.api.ContentCreat+json',
            'Accept' => 'application/vnd.ibexa.api.ContentCreat+json',
        ]);

        $subscriber = new SupportedMediaTypesSubscriber();
        $event = new RequestEvent($this->kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber->allowOnlySupportedMediaTypes($event);

        self::expectNotToPerformAssertions();
    }

    public function testThrowsExceptionWhenContentTypeHeaderTypeIsNotSupported(): void
    {
        $request = new Request();
        $request->attributes->set('supported_media_types', ['json']);
        $request->headers = new HeaderBag([
            'Content-Type' => 'application/vnd.ibexa.api.ContentCreate+xml',
        ]);

        $subscriber = new SupportedMediaTypesSubscriber();
        $event = new RequestEvent($this->kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $this->expectException(UnsupportedMediaTypeHttpException::class);
        $subscriber->allowOnlySupportedMediaTypes($event);
    }

    public function testThrowsExceptionWhenAcceptHeaderTypeIsNotSupported(): void
    {
        $request = new Request();
        $request->attributes->set('supported_media_types', ['json']);
        $request->headers = new HeaderBag([
            'Accept' => 'application/vnd.ibexa.api.ContentCreate+xml',
        ]);

        $subscriber = new SupportedMediaTypesSubscriber();
        $event = new RequestEvent($this->kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $this->expectException(UnsupportedMediaTypeHttpException::class);
        $subscriber->allowOnlySupportedMediaTypes($event);
    }
}
