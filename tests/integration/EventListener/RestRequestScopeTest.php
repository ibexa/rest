<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\Rest\EventListener;

use Ibexa\Bundle\Rest\EventListener\RequestListener;
use Ibexa\Contracts\Test\Core\IbexaKernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

#[CoversClass(RequestListener::class)]
final class RestRequestScopeTest extends IbexaKernelTestCase
{
    public function testV2ErrorsAreRenderedAsRestErrorMessages(): void
    {
        $response = $this->handle('/api/ibexa/v2/not-a-route');

        self::assertSame(404, $response->getStatusCode());
        self::assertStringStartsWith(
            'application/vnd.ibexa.api.ErrorMessage',
            (string)$response->headers->get('Content-Type')
        );
    }

    public function testV3ErrorsAreNotRenderedAsRestErrorMessages(): void
    {
        $response = $this->handle('/api/ibexa/v3/not-a-route');

        $contentType = (string)$response->headers->get('Content-Type');
        self::assertStringNotContainsString('ErrorMessage', $contentType);
        self::assertStringNotContainsString('+xml', $contentType);
        self::assertStringNotContainsString('ErrorMessage', (string)$response->getContent());
    }

    private function handle(string $uri): Response
    {
        return self::bootKernel()->handle(
            Request::create($uri),
            HttpKernelInterface::MAIN_REQUEST,
            true
        );
    }
}
