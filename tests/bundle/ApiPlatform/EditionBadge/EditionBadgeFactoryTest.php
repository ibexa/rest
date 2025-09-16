<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\Rest\ApiPlatform\EditionBadge;

use ApiPlatform\OpenApi\Model\Operation;
use Ibexa\Bundle\Rest\ApiPlatform\EditionBadge\EditionBadgeFactory;
use Ibexa\Tests\Bundle\Rest\DependencyInjection\EditionBadgesProcessorTest;
use LogicException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ibexa\Bundle\Rest\ApiPlatform\EditionBadge\EditionBadgeFactory
 *
 * @phpstan-import-type TBadgeList from \Ibexa\Bundle\Rest\ApiPlatform\EditionBadge\EditionBadgeFactoryInterface
 */
final class EditionBadgeFactoryTest extends TestCase
{
    private const array TAG_TO_EDITION_MAP = [
        'Foo' => ['foo'],
        'Bar' => ['foo', 'bar'],
        'Bar Baz' => ['bar-baz', 'bar'],
    ];

    private EditionBadgeFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new EditionBadgeFactory(
            EditionBadgesProcessorTest::BADGES_CONFIG,
            self::TAG_TO_EDITION_MAP
        );
    }

    /**
     * @dataProvider provideOperationBadgeData
     *
     * @param ?array<string> $tags
     *
     * @phpstan-param TBadgeList $expectedBadges
     */
    public function testGetBadgesForOperation(?array $tags, array $expectedBadges): void
    {
        $operation = new Operation(tags: $tags);
        self::assertEquals($expectedBadges, $this->factory->getBadgesForOperation($operation));
    }

    /**
     * @phpstan-return iterable<string, array{tags: ?array<string>, expectedBadges: TBadgeList}>
     */
    public static function provideOperationBadgeData(): iterable
    {
        yield 'single edition' => [
            'tags' => ['Foo'],
            'expectedBadges' => [
                [
                    'name' => 'Foo',
                    'color' => '#ff0000',
                ],
            ],
        ];

        yield 'multiple editions' => [
            'tags' => ['Bar'],
            'expectedBadges' => [
                [
                    'name' => 'Foo',
                    'color' => '#ff0000',
                ],
                [
                    'name' => 'Bar',
                    'color' => '#00ff00',
                ],
            ],
        ];

        yield 'no tags' => [
            'tags' => null,
            'expectedBadges' => [],
        ];

        yield 'non-existent tag' => [
            'tags' => ['NonExistent'],
            'expectedBadges' => [],
        ];
    }

    public function testGetBadgesForOperationWithInvalidEditionConfig(): void
    {
        $factory = new EditionBadgeFactory(
            [],
            ['Tag' => ['invalid_edition']]
        );

        $operation = new Operation(tags: ['Tag']);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('No badge configuration for invalid_edition for Tag tag');

        $factory->getBadgesForOperation($operation);
    }
}
