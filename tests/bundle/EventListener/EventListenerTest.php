<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\Rest\EventListener;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\HttpKernelInterface;

abstract class EventListenerTest extends TestCase
{
    /** @var \Symfony\Component\HttpFoundation\ParameterBag&\PHPUnit\Framework\MockObject\MockObject */
    protected ParameterBag $requestAttributesMock;

    protected bool $isRestRequest = true;

    protected int $requestType = HttpKernelInterface::MAIN_REQUEST;

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
                method_exists($eventListener, is_array($method) ? (is_array($method[0]) ? $method[0][0] : (string)$method[0]) : $method)
            );
        }
    }

    protected function getRequestAttributesMock(): MockObject & ParameterBag
    {
        $requestAttributesMock = $this->createMock(ParameterBag::class);

        $requestAttributesMock
            ->method('get')
            ->with('is_rest_request')
            ->willReturn($this->isRestRequest);

        $requestAttributesMock
            ->method('getBoolean')
            ->with('csrf_protection', true)
            ->willReturn($this->enableCsrfProtection);

        return $requestAttributesMock;
    }

    abstract protected function getEventListener(?bool $csrfEnabled = null): EventSubscriberInterface;

    /**
     * Returns an array with the events the listener should be subscribed to.
     *
     * @return array<mixed>
     */
    abstract public function provideExpectedSubscribedEventTypes(): array;
}
