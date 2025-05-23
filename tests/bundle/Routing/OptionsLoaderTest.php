<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Bundle\Rest\Routing;

use Ibexa\Bundle\Rest\Routing\OptionsLoader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouteCollection;

/**
 * @covers \Ibexa\Bundle\Rest\Routing\OptionsLoader
 */
class OptionsLoaderTest extends TestCase
{
    private OptionsLoader\RouteCollectionMapper & MockObject $routeCollectionMapperMock;

    /**
     * @dataProvider getResourceType
     */
    public function testSupportsResourceType(string $type, bool $expected): void
    {
        self::assertEquals(
            $expected,
            $this->getOptionsLoader()->supports(null, $type)
        );
    }

    /**
     * @return array<array{string, bool}>
     */
    public function getResourceType(): array
    {
        return [
            ['rest_options', true],
            ['something else', false],
        ];
    }

    public function testLoad(): void
    {
        $optionsRouteCollection = new RouteCollection();

        $this->getRouteCollectionMapperMock()->expects(self::once())
            ->method('mapCollection')
            ->with(new RouteCollection())
            ->willReturn($optionsRouteCollection);

        self::assertSame(
            $optionsRouteCollection,
            $this->getOptionsLoader()->load('resource', 'rest_options')
        );
    }

    /**
     * Returns a partially mocked OptionsLoader, with the import method mocked.
     */
    protected function getOptionsLoader(): OptionsLoader & MockObject
    {
        $mock = $this->getMockBuilder(OptionsLoader::class)
            ->setConstructorArgs([$this->getRouteCollectionMapperMock()])
            ->setMethods(['import'])
            ->getMock();

        $mock->expects(self::any())
            ->method('import')
            ->with(self::anything(), self::anything())
            ->willReturn(new RouteCollection());

        return $mock;
    }

    protected function getRouteCollectionMapperMock(): OptionsLoader\RouteCollectionMapper & MockObject
    {
        if (!isset($this->routeCollectionMapperMock)) {
            $this->routeCollectionMapperMock = $this->createMock(OptionsLoader\RouteCollectionMapper::class);
        }

        return $this->routeCollectionMapperMock;
    }
}
