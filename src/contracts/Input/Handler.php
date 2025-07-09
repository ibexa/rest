<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Contracts\Rest\Input;

/**
 * Input format handler base class.
 */
abstract class Handler
{
    /**
     * Converts the given string to an array structure.
     */
    abstract public function convert(string $string): array|string|int|bool|float|null;
}
