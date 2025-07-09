<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\FieldTypeProcessor;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\Rest\FieldTypeProcessor\RelationListProcessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

class RelationListProcessorTest extends TestCase
{
    /** @var string[] */
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
                    ['selectionMethod' => constant("Ibexa\\Core\\FieldType\\RelationList\\Type::{$constantName}")],
                ];
            },
            $this->constants
        );
    }

    /**
     * @covers \Ibexa\Rest\FieldTypeProcessor\RelationListProcessor::preProcessFieldSettingsHash
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
     * @covers \Ibexa\Rest\FieldTypeProcessor\RelationListProcessor::postProcessFieldSettingsHash
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

    public function testPostProcessFieldSettingsHashLocation(): void
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

        $hash = $processor->postProcessFieldSettingsHash(['selectionDefaultLocation' => 42]);

        self::assertEquals([
            'selectionDefaultLocation' => 42,
            'selectionDefaultLocationHref' => '/api/ibexa/v2/content/locations/1/25/42',
        ], $hash);

        //empty cases
        $hash = $processor->postProcessFieldSettingsHash(['selectionDefaultLocation' => '']);
        self::assertEquals(['selectionDefaultLocation' => ''], $hash);
        $hash = $processor->postProcessFieldSettingsHash(['selectionDefaultLocation' => null]);
        self::assertEquals(['selectionDefaultLocation' => null], $hash);
    }

    public function testPostProcessValueHash(): void
    {
        $processor = $this->getProcessor();

        $routerMock = $this->createMock(RouterInterface::class);
        $processor->setRouter($routerMock);

        $routerMock
            ->expects(self::exactly(2))
            ->method('generate')
            ->withConsecutive(
                ['ibexa.rest.load_content', ['contentId' => 42]],
                ['ibexa.rest.load_content', ['contentId' => 300]]
            )->willReturnOnConsecutiveCalls(
                '/api/ibexa/v2/content/objects/42',
                '/api/ibexa/v2/content/objects/300'
            );

        $hash = $processor->postProcessValueHash(['destinationContentIds' => [42, 300]]);
        self::assertArrayHasKey('destinationContentHrefs', $hash);
        self::assertEquals('/api/ibexa/v2/content/objects/42', $hash['destinationContentHrefs'][0]);
        self::assertEquals('/api/ibexa/v2/content/objects/300', $hash['destinationContentHrefs'][1]);
    }

    protected function getProcessor(): RelationListProcessor
    {
        return new RelationListProcessor();
    }
}
