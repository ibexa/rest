<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\Rest\EventListener;

use Ibexa\Bundle\Rest\EventListener\XmlUnsupportedMediaTypeSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class XmlUnsupportedMediaTypeSubscriberTest extends TestCase
{
    private const XML_REGEXP = '(^application/vnd\.ibexa\.api(\.[A-Za-z]+)+\+xml$)';

    /** @var \Symfony\Component\HttpKernel\HttpKernelInterface&\PHPUnit\Framework\MockObject\MockObject */
    private HttpKernelInterface $kernel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kernel = $this->createMock(HttpKernelInterface::class);
    }

    public function testDoesNothingWhenXmlDisabledIsNotTrue(): void
    {
        $request = new Request();
        $request->attributes->set('xml_disabled', false);

        $event = new RequestEvent($this->kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber = new XmlUnsupportedMediaTypeSubscriber([self::XML_REGEXP]);
        $subscriber->blockXmlUnsupportedMediaType($event);

        self::expectNotToPerformAssertions();
    }

    public function testDoesNothingWhenNoRegexps(): void
    {
        $request = new Request();
        $request->attributes->set('xml_disabled', true);

        $subscriber = new XmlUnsupportedMediaTypeSubscriber([]);
        $event = new RequestEvent($this->kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber->blockXmlUnsupportedMediaType($event);

        self::expectNotToPerformAssertions();
    }

    public function testDoesNothingWhenNoMatch(): void
    {
        $request = new Request();
        $request->attributes->set('xml_disabled', true);
        $request->headers = new HeaderBag([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);

        $subscriber = new XmlUnsupportedMediaTypeSubscriber([self::XML_REGEXP]);
        $event = new RequestEvent($this->kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber->blockXmlUnsupportedMediaType($event);

        self::expectNotToPerformAssertions();
    }

    public function testThrowsExceptionWhenContentTypeHeaderMatches(): void
    {
        $request = new Request();
        $request->attributes->set('xml_disabled', true);
        $request->headers = new HeaderBag([
            'Content-Type' => 'application/vnd.ibexa.api.ContentCreate+xml',
        ]);

        $subscriber = new XmlUnsupportedMediaTypeSubscriber([self::XML_REGEXP]);
        $event = new RequestEvent($this->kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $this->expectException(UnsupportedMediaTypeHttpException::class);
        $subscriber->blockXmlUnsupportedMediaType($event);
    }

    public function testThrowsExceptionWhenAcceptHeaderMatches(): void
    {
        $request = new Request();
        $request->attributes->set('xml_disabled', true);
        $request->headers = new HeaderBag([
            'Accept' => 'application/vnd.ibexa.api.ContentCreate+xml',
        ]);

        $subscriber = new XmlUnsupportedMediaTypeSubscriber([self::XML_REGEXP]);
        $event = new RequestEvent($this->kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $this->expectException(UnsupportedMediaTypeHttpException::class);
        $subscriber->blockXmlUnsupportedMediaType($event);
    }
}
