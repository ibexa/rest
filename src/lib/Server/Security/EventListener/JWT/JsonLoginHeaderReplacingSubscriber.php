<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Security\EventListener\JWT;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * JWT authentication since Symfony 5.4 relies on `json_login` hence `application/json` header is required.
 * Therefore, there has to be a way to replace prior `application/vnd.ibexa.api.JWTInput+json` header whenever JWT authentication
 * is triggered.
 */
final readonly class JsonLoginHeaderReplacingSubscriber implements EventSubscriberInterface
{
    private const string CONTENT_TYPE_HEADER = 'Content-Type';

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['replaceJsonLoginHeader', 10],
        ];
    }

    public function replaceJsonLoginHeader(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$request->headers->has(self::CONTENT_TYPE_HEADER)) {
            return;
        }

        if ($request->headers->get(self::CONTENT_TYPE_HEADER) !== 'application/vnd.ibexa.api.JWTInput+json') {
            return;
        }

        $request->headers->set(self::CONTENT_TYPE_HEADER, 'application/json');
    }
}
