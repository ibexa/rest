<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Bundle\Rest\Routing;

use Ibexa\Bundle\Rest\Routing\OptionsLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouteCollection;

#[CoversClass(OptionsLoader::class)]
class OptionsLoaderTest extends TestCase
{
    #[DataProvider('getResourceType')]
    public function testSupportsResourceType(
        string $type,
        bool $expected
    ): void {
        self::assertEquals(
            $expected,
            (new OptionsLoader(self::createStub(OptionsLoader\RouteCollectionMapper::class)))->supports(null, $type)
        );
    }

    /**
     * @return array<array{string, bool}>
     */
    public static function getResourceType(): array
    {
        return [
            ['rest_options', true],
            ['something else', false],
        ];
    }

    public function testLoad(): void
    {
        $optionsRouteCollection = new RouteCollection();

        $routeCollectionMapperMock = $this->createMock(OptionsLoader\RouteCollectionMapper::class);
        $routeCollectionMapperMock->expects($this->once())
            ->method('mapCollection')
            ->with(new RouteCollection())
            ->willReturn($optionsRouteCollection);

        self::assertSame(
            $optionsRouteCollection,
            $this->getOptionsLoader($routeCollectionMapperMock)->load('resource', 'rest_options')
        );
    }

    /**
     * Returns a partially mocked OptionsLoader, with the import method mocked.
     */
    protected function getOptionsLoader(OptionsLoader\RouteCollectionMapper $routeCollectionMapper): OptionsLoader & MockObject
    {
        $mock = $this->getMockBuilder(OptionsLoader::class)
            ->setConstructorArgs([$routeCollectionMapper])
            ->onlyMethods(['import'])
            ->getMock();

        $mock->expects($this->once())
            ->method('import')
            ->with(self::anything(), self::anything())
            ->willReturn(new RouteCollection());

        return $mock;
    }
}
