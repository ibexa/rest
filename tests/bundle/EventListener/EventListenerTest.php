<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\Rest\EventListener;

use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

abstract class EventListenerTest extends TestCase
{
    /** @var \Symfony\Component\HttpFoundation\Request|\PHPUnit\Framework\MockObject\MockObject */
    protected Request $requestMock;

    /** @var \Symfony\Component\HttpFoundation\ParameterBag|\PHPUnit\Framework\MockObject\MockObject */
    protected ParameterBag $requestAttributesMock;

    /** @var \Symfony\Component\HttpFoundation\ParameterBag|\PHPUnit\Framework\MockObject\MockObject */
    protected ParameterBag $requestHeadersMock;

    protected bool $isRestRequest = true;

    protected int $requestType = HttpKernelInterface::MAIN_REQUEST;

    protected $requestMethod = false;

    protected bool $enableCsrfProtection = true;

    /**
     * @param array<mixed> $expectedEventTypes
     *
     * @dataProvider provideExpectedSubscribedEventTypes
     */
    public function testGetSubscribedEvents(array $expectedEventTypes): void
    {
        $eventListener = $this->getEventListener();

        $supportedEvents = $eventListener->getSubscribedEvents();
        $supportedEventTypes = array_keys($supportedEvents);
        sort($supportedEventTypes);
        sort($expectedEventTypes);

        self::assertEquals($expectedEventTypes, $supportedEventTypes);

        // Check that referenced methods exist
        foreach ($supportedEvents as $method) {
            self::assertTrue(
                method_exists($eventListener, is_array($method) ? $method[0] : $method)
            );
        }
    }

    /**
     * @return \Symfony\Component\HttpFoundation\ParameterBag|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getRequestAttributesMock(): ParameterBag
    {
        if (!isset($this->requestAttributesMock)) {
            $this->requestAttributesMock = $this->createMock(ParameterBag::class);
            $this->requestAttributesMock
                ->expects(self::once())
                ->method('get')
                ->with('is_rest_request')
                ->willReturn($this->isRestRequest);

            $this->requestAttributesMock
                ->method('getBoolean')
                ->with('csrf_protection', true)
                ->willReturn($this->enableCsrfProtection);
        }

        return $this->requestAttributesMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\HttpFoundation\Request
     */
    protected function getRequestMock(): Request
    {
        if (!isset($this->requestMock)) {
            $this->requestMock = $this->createMock(Request::class);
            $this->requestMock->attributes = $this->getRequestAttributesMock();
            $this->requestMock->headers = $this->getRequestHeadersMock();

            if ($this->requestMethod === false) {
                $this->requestMock
                    ->expects(self::never())
                    ->method('getMethod');
            } else {
                $this->requestMock
                    ->expects(self::atLeastOnce())
                    ->method('getMethod')
                    ->willReturn($this->requestMethod);
            }
        }

        return $this->requestMock;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\ParameterBag|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getRequestHeadersMock(): ParameterBag
    {
        if (!isset($this->requestHeadersMock)) {
            $this->requestHeadersMock = $this->createMock(ParameterBag::class);
        }

        return $this->requestHeadersMock;
    }

    abstract protected function getEventListener(?bool $csrfEnabled = null): EventSubscriberInterface;

    /**
     * Returns an array with the events the listener should be subscribed to.
     *
     * @return array<mixed>
     */
    abstract public function provideExpectedSubscribedEventTypes(): array;
}
