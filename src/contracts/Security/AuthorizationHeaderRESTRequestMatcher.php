<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Rest\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher;

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

        if (
            $request->attributes->get('_route') === 'ibexa.rest.create_token'
            || !empty($request->headers->get($this->headerName ?? 'Authorization'))
        ) {
            return parent::matches($request);
        }

        return false;
    }
}
