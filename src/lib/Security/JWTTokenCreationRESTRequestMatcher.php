<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

/**
 * @internal
 *
 * This class is mandatory for JWT REST calls recognition. It's used within security.firewalls.ibexa_jwt_rest.request_matcher configuration key.
 */
final class JWTTokenCreationRESTRequestMatcher implements RequestMatcherInterface
{
    public function matches(Request $request): bool
    {
        if ($request->attributes->get('is_rest_request', false) !== true) {
            return false;
        }

        return $request->attributes->get('_route') === 'ibexa.rest.create_token';
    }
}
