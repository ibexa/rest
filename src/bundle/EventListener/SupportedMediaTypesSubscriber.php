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

final class SupportedMediaTypesSubscriber implements EventSubscriberInterface
{
    private const SUPPORTED_MEDIA_TYPES_PATTERN = '(^application/vnd\.ibexa\.api(\.[A-Za-z]+)+\+%s$)';

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
        $regexps = array_map(
            static fn (string $mediaType): string => sprintf(
                self::SUPPORTED_MEDIA_TYPES_PATTERN,
                strtolower($mediaType)
            ),
            $supportedMediaTypes
        );

        $contentTypeHeader = $request->headers->get('Content-Type') ?? '';
        $acceptHeader = $request->headers->get('Accept') ?? '';

        foreach ($regexps as $regexp) {
            if (
                preg_match($regexp, $contentTypeHeader) === 1
                || preg_match($regexp, $acceptHeader) === 1
            ) {
                break;
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
    }
}
