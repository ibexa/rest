<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\Rest\Functional;

final class ErrorMessageTest extends TestCase
{
    private const UNKNOWN_API_URI = '/api/invalid/v1/test';

    protected $autoLogin = false;

    public function testUnknownApiUriReturnsErrorMessageForRestClient(): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', self::UNKNOWN_API_URI, '', 'ErrorMessage+json')
        );

        self::assertHttpResponseCodeEquals($response, 404);
        self::assertStringStartsWith(
            'application/vnd.ibexa.api.ErrorMessage+json',
            $response->getHeaderLine('Content-Type')
        );
    }

    public function testUnknownApiUriReturnsErrorPageForClientExpectingHtml(): void
    {
        $request = $this->createHttpRequest('GET', self::UNKNOWN_API_URI)
            ->withHeader('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8');
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 404);
        self::assertStringStartsWith('text/html', $response->getHeaderLine('Content-Type'));
    }
}
