<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\Rest\Routing;

use Ibexa\Bundle\Rest\Routing\OptionsLoader\RouteCollectionMapper;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

/**
 * Goes through all REST routes, and registers new routes for all routes
 * a new one with the OPTIONS method.
 */
class OptionsLoader extends Loader
{
    protected RouteCollectionMapper $routeCollectionMapper;

    public function __construct(
        RouteCollectionMapper $mapper,
        ?string $env = null,
    ) {
        parent::__construct($env);

        $this->routeCollectionMapper = $mapper;
    }

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        return $this->routeCollectionMapper->mapCollection($this->import($resource));
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return $type === 'rest_options';
    }
}
