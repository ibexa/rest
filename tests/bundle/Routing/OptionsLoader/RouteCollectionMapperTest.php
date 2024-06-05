<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Bundle\Rest\Routing\OptionsLoader;

use Ibexa\Bundle\Rest\Routing\OptionsLoader\Mapper;
use Ibexa\Bundle\Rest\Routing\OptionsLoader\RouteCollectionMapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * @covers \Ibexa\Bundle\Rest\Routing\OptionsLoader\RouteCollectionMapper
 */
class RouteCollectionMapperTest extends TestCase
{
    /** @var \Ibexa\Bundle\Rest\Routing\OptionsLoader\RouteCollectionMapper */
    protected $collectionMapper;

    public function setUp(): void
    {
        $this->collectionMapper = new RouteCollectionMapper(
            new Mapper()
        );
    }

    public function testAddRestRoutesCollection()
    {
        $restRoutesCollection = new RouteCollection();
        $restRoutesCollection->add('ibexa.rest.route_one_get', $this->createRoute('/route/one', ['GET']));
        $restRoutesCollection->add('ibexa.rest.route_one_post', $this->createRoute('/route/one', ['POST']));
        $restRoutesCollection->add('ibexa.rest.route_two_delete', $this->createRoute('/route/two', ['DELETE']));

        $optionsRouteCollection = $this->collectionMapper->mapCollection($restRoutesCollection);

        self::assertEquals(
            2,
            $optionsRouteCollection->count()
        );

        self::assertInstanceOf(
            Route::class,
            $optionsRouteCollection->get('ibexa.rest.options.route_one')
        );

        self::assertInstanceOf(
            Route::class,
            $optionsRouteCollection->get('ibexa.rest.options.route_two')
        );

        self::assertEquals(
            'GET,POST',
            $optionsRouteCollection->get('ibexa.rest.options.route_one')->getDefault('allowedMethods')
        );

        self::assertEquals(
            'DELETE',
            $optionsRouteCollection->get('ibexa.rest.options.route_two')->getDefault('allowedMethods')
        );
    }

    public function testAddRestRoutesCollectionWithConditionAndSuffix(): void
    {
        $restRoutesCollection = new RouteCollection();
        $restRoutesCollection->add(
            'ibexa.rest.route_three_post',
            $this->createRoute(
                '/route/three',
                ['POST'],
                'ibexa_get_media_type(request) === "RouteThreeInput"',
                ['options_route_suffix' => 'RouteThreeInput'],
            ),
        );

        $optionsRouteCollection = $this->collectionMapper->mapCollection($restRoutesCollection);

        self::assertCount(1, $optionsRouteCollection);

        $optionsRoute = $optionsRouteCollection->get('ibexa.rest.options.route_three.RouteThreeInput');

        self::assertInstanceOf(Route::class, $optionsRoute);

        self::assertEquals('POST', $optionsRoute->getDefault('allowedMethods'));
    }

    /**
     * @param array<string> $methods
     * @param array<string> $options
     */
    private function createRoute(
        string $path,
        array $methods,
        ?string $condition = null,
        array $options = [],
    ): Route {
        return new Route($path, [], [], $options, '', [], $methods, $condition);
    }
}
