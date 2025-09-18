<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\Rest\ApiPlatform\EditionBadge;

use ApiPlatform\OpenApi\Model\Operation;
use LogicException;

/**
 * @internal
 *
 * @phpstan-type TBadgeData array{name: string, color: string, position?: 'before'|'after'}
 * @phpstan-type TBadgeConfig array{name: string, color: string, position?: 'before'|'after'}
 * @phpstan-type TBadgesConfig array<string, TBadgeConfig>
 * @phpstan-type TTagToEditionMap array<string, string[]>
 */
final readonly class EditionBadgeFactory implements EditionBadgeFactoryInterface
{
    /**
     * @phpstan-param TBadgesConfig $badgesConfig
     * @phpstan-param TTagToEditionMap $tagToEditionMap
     */
    public function __construct(private array $badgesConfig, private array $tagToEditionMap)
    {
    }

    public function getBadgesForOperation(Operation $operation): array
    {
        $badges = [];
        foreach ($operation->getTags() ?? [] as $tag) {
            $tagEditions = $this->tagToEditionMap[$tag] ?? [];
            foreach ($tagEditions as $tagEdition) {
                if (!isset($this->badgesConfig[$tagEdition])) {
                    // gets also validated when processing configuration, so theoretically should never happen here
                    throw new LogicException("No badge configuration for $tagEdition for $tag tag");
                }

                $badges[] = $this->buildBadgeDataFromConfig($this->badgesConfig[$tagEdition]);
            }
        }

        return $badges;
    }

    /**
     * @phpstan-param TBadgeConfig $badgeConfig
     *
     * @phpstan-return TBadgeData
     */
    private function buildBadgeDataFromConfig(array $badgeConfig): array
    {
        $badge = [
            'name' => $badgeConfig['name'],
            'color' => $badgeConfig['color'],
        ];
        if (isset($badgeConfig['position'])) {
            $badge['position'] = $badgeConfig['position'];
        }

        return $badge;
    }
}
