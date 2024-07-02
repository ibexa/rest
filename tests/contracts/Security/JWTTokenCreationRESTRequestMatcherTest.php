<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Contracts\Rest\Security;

use Ibexa\Rest\Security\JWTTokenCreationRESTRequestMatcher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class JWTTokenCreationRESTRequestMatcherTest extends TestCase
{
    public function testDoesNotMatchNonRestRequests(): void
    {
        $matcher = new JWTTokenCreationRESTRequestMatcher();

        self::assertFalse($matcher->matches(new Request()));
    }

    public function testDoesNotMatchRestRequestsWithoutHeader(): void
    {
        $matcher = new JWTTokenCreationRESTRequestMatcher();

        $request = $this->createRequest([
            'is_rest_request' => true,
        ]);

        self::assertFalse($matcher->matches($request));
    }

    public function testMatchesRestJwtCreationEndpoint(): void
    {
        $matcher = new JWTTokenCreationRESTRequestMatcher();

        $request = $this->createRequest([
            'is_rest_request' => true,
            '_route' => 'ibexa.rest.create_token',
        ]);

        self::assertTrue($matcher->matches($request));
    }

    /**
     * @param array<string, mixed> $attributes
     * @param array<string, array<string>|string> $server
     */
    private function createRequest(array $attributes = [], array $server = []): Request
    {
        return new Request([], [], $attributes, [], [], $server);
    }
}
