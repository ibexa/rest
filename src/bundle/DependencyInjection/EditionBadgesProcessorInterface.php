<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\Rest\DependencyInjection;

/**
 * @internal
 *
 * @phpstan-type TTagToEditionMappingConfig list<array{tag: string, editions: string[]}>
 */
interface EditionBadgesProcessorInterface
{
    /**
     * @phpstan-param TTagToEditionMappingConfig $tagToEditionMappingConfig
     */
    public function process(array $tagToEditionMappingConfig): void;
}
