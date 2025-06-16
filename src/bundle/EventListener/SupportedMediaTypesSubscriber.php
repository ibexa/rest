<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\Rest\EventListener;

use RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

final class SupportedMediaTypesSubscriber implements EventSubscriberInterface
{
    private const SUPPORTED_MEDIA_TYPES_REGEX = '/(?<=\+)[A-Za-z0-9]+/';

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['allowOnlySupportedMediaTypes', 0],
            ],
        ];
    }

    public function allowOnlySupportedMediaTypes(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$request->attributes->has('supported_media_types')) {
            return;
        }

        $supportedMediaTypes = $request->attributes->get('supported_media_types');
        if (empty($supportedMediaTypes)) {
            return;
        }

        $contentTypeHeader = $request->headers->get('Content-Type') ?? '';
        $acceptHeader = $request->headers->get('Accept') ?? '';

        try {
            $isContentTypeHeaderSupported = $this->isMediaTypeSupported(
                $contentTypeHeader,
                $supportedMediaTypes
            );

            $isAcceptHeaderSupported = $this->isMediaTypeSupported(
                $acceptHeader,
                $supportedMediaTypes
            );

            if ($isContentTypeHeaderSupported && $isAcceptHeaderSupported) {
                return;
            }
        } catch (RuntimeException $e) {
            return;
        }

        throw new UnsupportedMediaTypeHttpException(
            sprintf(
                'Unsupported media type was used. Available ones are: %s',
                implode(', ', $supportedMediaTypes)
            ),
            null,
            Response::HTTP_UNSUPPORTED_MEDIA_TYPE
        );
    }

    /**
     * @param string[] $supportedMediaTypes
     */
    private function isMediaTypeSupported(
        string $header,
        array $supportedMediaTypes
    ): bool {
        preg_match(self::SUPPORTED_MEDIA_TYPES_REGEX, $header, $matches);

        $match = reset($matches);
        if ($match === false) {
            throw new RuntimeException(sprintf(
                'Failed to extract media type from header: %s',
                $header
            ));
        }

        return in_array($match, $supportedMediaTypes, true);
    }
}
