<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\Rest\EventListener;

use Ibexa\Rest\Server\View\AcceptHeaderVisitorDispatcher;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Throwable;

/**
 * REST Response Listener.
 *
 * Converts responses from REST controllers to REST Responses, depending on the Accept-Header value.
 */
final class ResponseListener implements EventSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private AcceptHeaderVisitorDispatcher $viewDispatcher;

    public function __construct(AcceptHeaderVisitorDispatcher $viewDispatcher)
    {
        $this->viewDispatcher = $viewDispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => 'onKernelResultView',
            // Must happen BEFORE the Core ExceptionListener.
            KernelEvents::EXCEPTION => ['onKernelExceptionView', 20],
        ];
    }

    public function onKernelResultView(ViewEvent $event): void
    {
        if (!$event->getRequest()->attributes->get('is_rest_request')) {
            return;
        }

        $event->setResponse(
            $this->viewDispatcher->dispatch(
                $event->getRequest(),
                $event->getControllerResult()
            )
        );
        $event->stopPropagation();
    }

    /**
     * @throws \Exception
     */
    public function onKernelExceptionView(ExceptionEvent $event): void
    {
        if (!$event->getRequest()->attributes->get('is_rest_request')) {
            return;
        }

        $exception = $event->getThrowable();
        $this->logException($exception);

        $event->setResponse(
            $this->viewDispatcher->dispatch(
                $event->getRequest(),
                $exception
            )
        );
    }

    private function logException(Throwable $exception): void
    {
        if (!isset($this->logger)) {
            return;
        }

        $logLevel = LogLevel::ERROR;
        if (!$exception instanceof HttpExceptionInterface || $exception->getStatusCode() >= 500) {
            $logLevel = LogLevel::CRITICAL;
        }

        $this->logger->log($logLevel, $exception->getMessage(), [
            'exception' => $exception,
        ]);
    }
}
