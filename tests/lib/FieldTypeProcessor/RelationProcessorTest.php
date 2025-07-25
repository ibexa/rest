<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\FieldTypeProcessor;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\Rest\FieldTypeProcessor\RelationProcessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

class RelationProcessorTest extends TestCase
{
    /**
     * @var array<string>
     */
    protected array $constants = [
        'SELECTION_BROWSE',
        'SELECTION_DROPDOWN',
    ];

    /**
     * @return array<array{array{selectionMethod: string}, array{selectionMethod: mixed}}>
     */
    public function fieldSettingsHashes(): array
    {
        return array_map(
            static function ($constantName): array {
                return [
                    ['selectionMethod' => $constantName],
                    ['selectionMethod' => constant("Ibexa\\Core\\FieldType\\Relation\\Type::{$constantName}")],
                ];
            },
            $this->constants
        );
    }

    /**
     * @covers \Ibexa\Rest\FieldTypeProcessor\RelationProcessor::preProcessFieldSettingsHash
     *
     * @dataProvider fieldSettingsHashes
     *
     * @param array<string, mixed> $inputSettings
     * @param array<string, mixed> $outputSettings
     */
    public function testPreProcessFieldSettingsHash(array $inputSettings, array $outputSettings): void
    {
        $processor = $this->getProcessor();

        self::assertEquals(
            $outputSettings,
            $processor->preProcessFieldSettingsHash($inputSettings)
        );
    }

    /**
     * @covers \Ibexa\Rest\FieldTypeProcessor\RelationProcessor::postProcessFieldSettingsHash
     *
     * @dataProvider fieldSettingsHashes
     *
     * @param array<string, mixed> $inputSettings
     * @param array<string, mixed> $outputSettings
     */
    public function testPostProcessFieldSettingsHash(array $outputSettings, array $inputSettings): void
    {
        $processor = $this->getProcessor();

        self::assertEquals(
            $outputSettings,
            $processor->postProcessFieldSettingsHash($inputSettings)
        );
    }

    public function testpostProcessFieldSettingsHashLocation(): void
    {
        $processor = $this->getProcessor();

        $serviceLocationMock = $this->createMock(LocationService::class);
        $processor->setLocationService($serviceLocationMock);

        $serviceLocationMock
            ->method('loadLocation')
            ->with('42')
            ->willReturn(new Location(['path' => ['1', '25', '42'], 'pathString' => '1/25/42']));

        $routerMock = $this->createMock(RouterInterface::class);
        $processor->setRouter($routerMock);

        $routerMock
            ->method('generate')
            ->with('ibexa.rest.load_location', ['locationPath' => '1/25/42'])
            ->willReturn('/api/ibexa/v2/content/locations/1/25/42');

        $hash = $processor->postProcessFieldSettingsHash(['selectionRoot' => 42]);

        self::assertEquals([
            'selectionRoot' => 42,
            'selectionRootHref' => '/api/ibexa/v2/content/locations/1/25/42',
        ], $hash);

        //empty cases
        $hash = $processor->postProcessFieldSettingsHash(['selectionRoot' => '']);
        self::assertEquals(['selectionRoot' => ''], $hash);
        $hash = $processor->postProcessFieldSettingsHash(['selectionRoot' => null]);
        self::assertEquals(['selectionRoot' => null], $hash);
    }

    public function testPostProcessFieldValueHash(): void
    {
        $processor = $this->getProcessor();

        $routerMock = $this->createMock(RouterInterface::class);
        $processor->setRouter($routerMock);

        $routerMock
            ->expects(self::once())
            ->method('generate')
            ->with('ibexa.rest.load_content', ['contentId' => 42])
            ->willReturn('/api/ibexa/v2/content/objects/42');

        $hash = $processor->postProcessValueHash(['destinationContentId' => 42]);
        self::assertArrayHasKey('destinationContentHref', $hash);
        self::assertEquals('/api/ibexa/v2/content/objects/42', $hash['destinationContentHref']);
    }

    public function testPostProcessFieldValueHashNullValue(): void
    {
        $processor = $this->getProcessor();

        $routerMock = $this->createMock(RouterInterface::class);
        $processor->setRouter($routerMock);

        $routerMock
            ->expects(self::never())
            ->method('generate');

        $hash = $processor->postProcessValueHash(['destinationContentId' => null]);
        self::assertArrayNotHasKey('destinationContentHref', $hash);
    }

    public function testPostProcessFieldValueHashNotAccessibleLocation(): void
    {
        $processor = $this->getProcessor();

        $serviceLocationMock = $this->createMock(LocationService::class);
        $processor->setLocationService($serviceLocationMock);

        $serviceLocationMock
            ->method('loadLocation')
            ->with('-1')
            ->willThrowException(new NotFoundException('', ''));

        $routerMock = $this->createMock(RouterInterface::class);
        $processor->setRouter($routerMock);

        $routerMock
            ->expects(self::never())
            ->method('generate');

        $hash = $processor->postProcessFieldSettingsHash(['selectionRoot' => -1]);

        self::assertSame([
            'selectionRoot' => -1,
            'selectionRootHref' => '',
        ], $hash);
    }

    protected function getProcessor(): RelationProcessor
    {
        return new RelationProcessor();
    }
}
