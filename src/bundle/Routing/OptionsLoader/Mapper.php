<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\Rest\Routing\OptionsLoader;

use Symfony\Component\Routing\Route;

/**
 * Maps a standard REST route to its OPTIONS equivalent.
 */
class Mapper
{
    public function mapRoute(Route $route): Route
    {
        $optionsRoute = clone $route;
        $optionsRoute->setMethods(['OPTIONS']);
        $optionsRoute->setDefault(
            '_controller',
            'Ibexa\Rest\Server\Controller\Options::getRouteOptions'
        );

        $optionsRoute->setDefault(
            'allowedMethods',
            implode(',', $route->getMethods())
        );

        return $optionsRoute;
    }

    /**
     * Merges the methods from $restRoute into the _method default of $optionsRoute.
     *
     * @param \Symfony\Component\Routing\Route $restRoute
     * @param \Symfony\Component\Routing\Route $optionsRoute
     *
     * @return \Symfony\Component\Routing\Route $optionsRoute with the methods from $restRoute in the _methods default
     */
    public function mergeMethodsDefault(Route $optionsRoute, Route $restRoute)
    {
        $mergedRoute = clone $optionsRoute;
        $mergedRoute->setDefault(
            'allowedMethods',
            implode(
                ',',
                array_unique(
                    array_merge(
                        explode(',', $optionsRoute->getDefault('allowedMethods')),
                        $restRoute->getMethods()
                    )
                )
            )
        );

        return $mergedRoute;
    }

    /**
     * Returns the OPTIONS name of a REST route.
     */
    public function getOptionsRouteName(Route $route): string
    {
        $name = str_replace('/', '_', $route->getPath());

        $parts = [
            'ibexa.rest.options',
            trim($name, '_'),
        ];

        // Routes that share path 1-to-1 can result in overwrite.
        // Use "options_route_suffix" to ensure uniqueness.
        $routeSuffix = $route->getOption('options_route_suffix');
        if ($routeSuffix !== null) {
            $parts[] = $routeSuffix;
        }

        return implode('.', $parts);
    }
}
