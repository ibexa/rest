<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher;

/**
 * @internal
 *
 * This class is mandatory for JWT REST calls recognition. It's used within security.firewalls.ibexa_jwt_rest.request_matcher configuration key.
 */
final class AuthorizationHeaderRESTRequestMatcher extends RequestMatcher
{
    private ?string $headerName;

    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        ?string $headerName = null,
        string $path = null,
        string $host = null,
        $methods = null,
        $ips = null,
        array $attributes = [],
        $schemes = null,
        int $port = null
    ) {
        parent::__construct($path, $host, $methods, $ips, $attributes, $schemes, $port);

        $this->headerName = $headerName;
    }

    public function matches(Request $request): bool
    {
        if ($request->attributes->get('is_rest_request', false) !== true) {
            return false;
        }

        if (!empty($request->headers->get($this->headerName ?? 'Authorization'))) {
            return parent::matches($request);
        }

        return false;
    }
}
