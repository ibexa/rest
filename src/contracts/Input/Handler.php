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
     *
     * @return array<mixed>|string|int|bool|float|null
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\Parser
     */
    abstract public function convert(string $string): array|string|int|bool|float|null;
}
