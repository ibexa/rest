<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\Rest\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;

/**
 * @internal
 *
 * @phpstan-import-type TBadgesConfig from \Ibexa\Bundle\Rest\ApiPlatform\EditionBadge\EditionBadgeFactory
 */
final readonly class EditionBadgesProcessor implements EditionBadgesProcessorInterface
{
    public const string TAG_EDITION_MAP_PARAMETER_NAME = 'ibexa.rest.edition_badges.badges.tag_editions.map';
    public const string BADGES_CONFIG_PARAMETER_NAME = 'ibexa.rest.edition_badges.badges.config';

    public function __construct(private ContainerBuilder $container)
    {
    }

    /**
     * Transforms a list of `array{tag, editions[]}` into a map of `tag => editions[]`.
     */
    public function process(array $tagToEditionMappingConfig): void
    {
        /** @phpstan-var TBadgesConfig $config */
        $config = $this->container->getParameter(self::BADGES_CONFIG_PARAMETER_NAME);
        $editions = array_keys($config);
        $tagToEditionMap = [];
        foreach ($tagToEditionMappingConfig as $tagToEditionMapping) {
            $unknownEditions = array_diff($tagToEditionMapping['editions'], $editions);
            if (!empty($unknownEditions)) {
                throw new LogicException(
                    sprintf(
                        'Unknown editions: %s. Expecting one of: %s',
                        implode(', ', $unknownEditions),
                        implode(', ', $editions)
                    )
                );
            }

            $tagToEditionMap[$tagToEditionMapping['tag']] = array_unique(
                array_merge(
                    $tagToEditionMap[$tagToEditionMapping['tag']] ?? [],
                    $tagToEditionMapping['editions']
                )
            );
        }

        $this->container->setParameter(self::TAG_EDITION_MAP_PARAMETER_NAME, $tagToEditionMap);
    }
}
