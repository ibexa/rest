<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Output\Normalizer;

use Ibexa\Rest\Server\Values\RestLocation;

final class TestData
{
    private string $name;

    private RestLocation $location;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getLocation(): RestLocation
    {
        return $this->location;
    }

    public function setLocation(RestLocation $location): void
    {
        $this->location = $location;
    }
}
