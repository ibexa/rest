<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\Rest\Serializer;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;

final readonly class TestDataObject
{
    public function __construct(
        public string $string,
        public int $int,
        public ?Location $apiLocation = null,
    ) {
    }
}
