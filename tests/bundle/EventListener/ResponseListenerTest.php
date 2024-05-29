<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\Rest\EventListener;

use Exception;
use Ibexa\Bundle\Rest\EventListener\ResponseListener;
use Ibexa\Rest\Server\View\AcceptHeaderVisitorDispatcher;
use stdClass;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class ResponseListenerTest extends EventListenerTest
{
    /** @var \Ibexa\Rest\Server\View\AcceptHeaderVisitorDispatcher|\PHPUnit\Framework\MockObject\MockObject */
    protected $visitorDispatcherMock;

    /** @var \stdClass */
    protected $eventValue;

    /** @var \Exception */
    protected $exceptionEventValue;

    protected $dispatcherMessage;

    protected $controllerResult;

    /** @var \Symfony\Component\HttpFoundation\Response */
    protected $response;

    protected EventDispatcherInterface $event;

    /** @var \Symfony\Component\HttpKernel\KernelInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $kernelMock;

    public function setUp(): void
    {
        $this->eventValue = new stdClass();
        $this->exceptionEventValue = new Exception();
        $this->response = new Response('BODY', 406, ['foo' => 'bar']);
    }

    public function provideExpectedSubscribedEventTypes(): array
    {
        return [
            [[KernelEvents::VIEW, KernelEvents::EXCEPTION]],
        ];
    }

    public function testOnKernelResultViewIsNotRestRequest(): void
    {
        $this->isRestRequest = false;

        $this->onKernelViewIsNotRestRequest(
            'onKernelResultView',
            $this->getViewEvent()
        );
    }

    public function testOnKernelExceptionViewIsNotRestRequest(): void
    {
        $this->isRestRequest = false;

        $this->onKernelViewIsNotRestRequest(
            'onKernelExceptionView',
            $this->getExceptionEvent()
        );
    }

    protected function onKernelViewIsNotRestRequest($method, RequestEvent $event): void
    {
        $this->getVisitorDispatcherMock()
            ->expects(self::never())
            ->method('dispatch');

        $this->getEventListener()->$method($event);
    }

    public function testOnKernelExceptionView(): void
    {
        $this->onKernelView('onKernelExceptionView', $this->getExceptionEvent(), $this->exceptionEventValue);
    }

    public function testOnControllerResultView(): void
    {
        $this->onKernelView('onKernelResultView', $this->getViewEvent(), $this->eventValue);
    }

    protected function onKernelView($method, $event, $value): void
    {
        $this->getVisitorDispatcherMock()
            ->expects(self::once())
            ->method('dispatch')
            ->with(
                $this->getRequestMock(),
                $value
            )->willReturn(
                $this->response
            );

        $this->getEventListener()->$method($event);

        self::assertEquals($this->response, $event->getResponse());
    }

    /**
     * @return \Ibexa\Rest\Server\View\AcceptHeaderVisitorDispatcher|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getVisitorDispatcherMock(): AcceptHeaderVisitorDispatcher
    {
        if (!isset($this->visitorDispatcherMock)) {
            $this->visitorDispatcherMock = $this->createMock(AcceptHeaderVisitorDispatcher::class);
        }

        return $this->visitorDispatcherMock;
    }

    protected function getEventListener(?bool $csrfEnabled = null): EventSubscriberInterface
    {
        return new ResponseListener(
            $this->getVisitorDispatcherMock()
        );
    }

    protected function getViewEvent(): ViewEvent
    {
        return new ViewEvent(
            $this->getKernelMock(),
            $this->getRequestMock(),
            HttpKernelInterface::MAIN_REQUEST,
            $this->eventValue
        );
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\HttpKernel\KernelInterface
     */
    protected function getKernelMock(): KernelInterface
    {
        if (!isset($this->kernelMock)) {
            $this->kernelMock = $this->createMock(KernelInterface::class);
        }

        return $this->kernelMock;
    }

    /**
     * @return \Symfony\Component\HttpKernel\Event\ExceptionEvent
     */
    private function getExceptionEvent(): ExceptionEvent
    {
        return new ExceptionEvent(
            $this->getKernelMock(),
            $this->getRequestMock(),
            HttpKernelInterface::MAIN_REQUEST,
            $this->exceptionEventValue
        );
    }
}
