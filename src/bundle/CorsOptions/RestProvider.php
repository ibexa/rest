<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\Rest\CorsOptions;

use Nelmio\CorsBundle\Options\ProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;

/**
 * REST Cors Options provider.
 *
 * Uses the REST OPTIONS routes allowedMethods attribute to provide the list of methods allowed for an URI.
 */
class RestProvider implements ProviderInterface
{
    protected RequestMatcherInterface $requestMatcher;

    public function __construct(RequestMatcherInterface $requestMatcher)
    {
        $this->requestMatcher = $requestMatcher;
    }

    /**
     * Returns allowed CORS methods for a REST route.
     *
     * @return array{allow_methods?: string[]}
     */
    public function getOptions(Request $request): array
    {
        $return = [];
        if ($request->attributes->has('is_rest_request') && $request->attributes->get('is_rest_request') === true) {
            $return['allow_methods'] = $this->getAllowedMethods($request->getPathInfo());
        }

        return $return;
    }

    /**
     * @return string[]
     */
    protected function getAllowedMethods(string $uri): array
    {
        try {
            $route = $this->requestMatcher->matchRequest(
                Request::create($uri, Request::METHOD_OPTIONS)
            );
            if (isset($route['allowedMethods'])) {
                return explode(',', $route['allowedMethods']);
            }
        } catch (ResourceNotFoundException $e) {
            // the provider doesn't care about a not found
        } catch (MethodNotAllowedException $e) {
            // neither does it care about a method not allowed
        }

        return [];
    }
}
