<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\Rest\ApiPlatform\EditionBadge;

use ApiPlatform\OpenApi\Model\Operation;

/**
 * @internal
 *
 * @phpstan-type TBadgeData array{name: string, color: string, position?: 'before'|'after'}
 * @phpstan-type TBadgeList list<TBadgeData>
 */
interface EditionBadgeFactoryInterface
{
    /**
     * @phpstan-return TBadgeList
     */
    public function getBadgesForOperation(Operation $operation): array;
}
