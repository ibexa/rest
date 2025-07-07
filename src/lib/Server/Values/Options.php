<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Contracts\Core\Repository\Values\ValueObject;

/**
 * Struct representing a resource OPTIONS response.
 */
class Options extends ValueObject
{
    /**
     * The methods allowed my the resource.
     *
     * @var array<int, string>
     */
    public array $allowedMethods;

    /**
     * @param array<int, string> $allowedMethods
     */
    public function __construct(array $allowedMethods)
    {
        $this->allowedMethods = $allowedMethods;
    }
}
