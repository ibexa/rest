<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\Rest\EventListener;

use Ibexa\Contracts\Rest\UriParser\UriParserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @internal
 *
 * REST request listener.
 *
 * Flags a REST request as such using the is_rest_request attribute.
 */
final class RequestListener implements EventSubscriberInterface
{
    private UriParserInterface $uriParser;

    public function __construct(UriParserInterface $uriParser)
    {
        $this->uriParser = $uriParser;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // 10001 is to ensure that REST requests are tagged before CorsListener is called
            KernelEvents::REQUEST => ['onKernelRequest', 10001],
        ];
    }

    /**
     * If the request is a REST one, sets the is_rest_request request attribute.
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $event->getRequest()->attributes->set(
            'is_rest_request',
            $this->uriParser->isRestRequest($event->getRequest())
        );
    }
}
