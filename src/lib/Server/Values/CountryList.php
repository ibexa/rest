<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Rest\Value as RestValue;

/**
 * Country list view model.
 */
class CountryList extends RestValue
{
    /**
     * @param array<string, array{Name: string, Alpha2: string, Alpha3: string, IDC: string}> $countries
     */
    public function __construct(
        private readonly array $countries
    ) {
    }

    /**
     * @return array<string, array{Name: string, Alpha2: string, Alpha3: string, IDC: string}>
     */
    public function getCountries(): array
    {
        return $this->countries;
    }
}
