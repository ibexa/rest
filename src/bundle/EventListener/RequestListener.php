<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\Rest\EventListener;

use Ibexa\Bundle\Rest\UriParser\UriParser;
use Ibexa\Contracts\Rest\UriParser\UriParserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @internal
 *
 * REST request listener.
 *
 * Flags a REST request as such using the is_rest_request attribute.
 */
class RequestListener implements EventSubscriberInterface
{
    /**
     * @deprecated rely on \Ibexa\Contracts\Rest\UriParser\UriParserInterface::isRestRequest instead.
     * @see \Ibexa\Contracts\Rest\UriParser\UriParserInterface::isRestRequest()
     */
    public const REST_PREFIX_PATTERN = UriParser::DEFAULT_REST_PREFIX_PATTERN;

    private UriParserInterface $uriParser;

    public function __construct(UriParserInterface $uriParser)
    {
        $this->uriParser = $uriParser;
    }

    /**
     * @return array
     */
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

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return bool
     *
     * @deprecated use \Ibexa\Contracts\Rest\UriParser\UriParserInterface::isRestRequest instead
     * @see \Ibexa\Contracts\Rest\UriParser\UriParserInterface::isRestRequest()
     */
    protected function hasRestPrefix(Request $request)
    {
        return preg_match(self::REST_PREFIX_PATTERN, $request->getPathInfo());
    }
}

class_alias(RequestListener::class, 'EzSystems\EzPlatformRestBundle\EventListener\RequestListener');
