<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\Rest\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

final class XmlUnsupportedMediaTypeSubscriber implements EventSubscriberInterface
{
    /** @var string[] */
    private array $xmlRegexps;

    /**
     * @param string[] $xmlRegexps
     */
    public function __construct(array $xmlRegexps)
    {
        $this->xmlRegexps = $xmlRegexps;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['blockXmlUnsupportedMediaType'],
            ],
        ];
    }

    public function blockXmlUnsupportedMediaType(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if ($request->attributes->get('xml_disabled') !== true) {
            return;
        }

        $regexps = reset($this->xmlRegexps);
        if ($regexps === false) {
            return;
        }

        $contentTypeHeader = $request->headers->get('Content-Type') ?? '';
        $acceptHeader = $request->headers->get('Accept') ?? '';

        if (
            preg_match($regexps, $contentTypeHeader) === 1
            || preg_match($regexps, $acceptHeader) === 1
        ) {
            throw new UnsupportedMediaTypeHttpException(
                'XML is not supported by this endpoint, use JSON instead.',
                null,
                Response::HTTP_UNSUPPORTED_MEDIA_TYPE
            );
        }
    }
}
