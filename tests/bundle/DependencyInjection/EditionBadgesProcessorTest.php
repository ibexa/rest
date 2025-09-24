<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\Rest\DependencyInjection;

use Ibexa\Bundle\Rest\DependencyInjection\EditionBadgesProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;

/**
 * @covers \Ibexa\Bundle\Rest\DependencyInjection\EditionBadgesProcessor
 *
 * @phpstan-import-type TTagToEditionMappingConfig from \Ibexa\Bundle\Rest\DependencyInjection\EditionBadgesProcessorInterface
 * @phpstan-import-type TTagToEditionMap from \Ibexa\Bundle\Rest\ApiPlatform\EditionBadge\EditionBadgeFactory
 */
final class EditionBadgesProcessorTest extends TestCase
{
    public const array BADGES_CONFIG = [
        'foo' => ['name' => 'Foo', 'color' => '#ff0000'],
        'bar' => ['name' => 'Bar', 'color' => '#00ff00'],
        'bar-baz' => ['name' => 'Bar Baz', 'color' => '#0000ff'],
    ];

    private ContainerBuilder & MockObject $containerBuilder;

    protected function setUp(): void
    {
        $this->containerBuilder = $this->createMock(ContainerBuilder::class);
        $this->containerBuilder
            ->method('hasParameter')
            ->with(EditionBadgesProcessor::BADGES_CONFIG_PARAMETER_NAME)
            ->willReturn(true);

        $this->containerBuilder
            ->method('getParameter')
            ->with(EditionBadgesProcessor::BADGES_CONFIG_PARAMETER_NAME)
            ->willReturn(self::BADGES_CONFIG);
    }

    /**
     * @phpstan-return iterable<string, array{0: TTagToEditionMappingConfig, 1: array<string, array<string>>}>
     */
    public static function getDataForTestProcess(): iterable
    {
        yield 'default use case' => [
            [
                ['tag' => 'Foo', 'editions' => ['foo', 'bar']],
                ['tag' => 'Bar', 'editions' => ['bar']],
            ],
            [
                'Foo' => ['foo', 'bar'],
                'Bar' => ['bar'],
            ],
        ];

        yield 'same tag multiple times' => [
            [
                ['tag' => 'Foo', 'editions' => ['foo', 'bar']],
                ['tag' => 'Bar', 'editions' => ['bar']],
                ['tag' => 'Foo', 'editions' => ['bar-baz']],
            ],
            [
                'Foo' => ['foo', 'bar', 'bar-baz'],
                'Bar' => ['bar'],
            ],
        ];

        yield 'duplicate editions' => [
            [
                ['tag' => 'Foo', 'editions' => ['foo', 'bar']],
                ['tag' => 'Foo', 'editions' => ['bar']],
            ],
            [
                'Foo' => ['foo', 'bar'],
            ],
        ];
    }

    /**
     * @dataProvider getDataForTestProcess
     *
     * @phpstan-param TTagToEditionMappingConfig $tagToEditionMappingConfig
     * @phpstan-param TTagToEditionMap $expectedTagToEditionMap
     */
    public function testProcess(array $tagToEditionMappingConfig, array $expectedTagToEditionMap): void
    {
        $processor = new EditionBadgesProcessor($this->containerBuilder);

        $this->containerBuilder
            ->expects(self::once())
            ->method('setParameter')
            ->with(EditionBadgesProcessor::TAG_EDITION_MAP_PARAMETER_NAME, $expectedTagToEditionMap);

        $processor->process($tagToEditionMappingConfig);
    }

    public function testProcessWithUnknownEdition(): void
    {
        $processor = new EditionBadgesProcessor($this->containerBuilder);

        $this->containerBuilder
            ->expects(self::never())
            ->method('setParameter');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Unknown editions: unknown1, unknown2. Expecting one of: foo, bar, bar-baz');

        $processor->process([['tag' => 'Foo', 'editions' => ['foo', 'unknown1', 'unknown2', 'bar']]]);
    }
}
