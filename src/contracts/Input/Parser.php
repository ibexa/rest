<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Contracts\Rest\Input;

/**
 * Base class for input parser.
 */
abstract class Parser
{
    /**
     * Parse input structure.
     *
     * @param array<mixed> $data
     */
    abstract public function parse(array $data, ParsingDispatcher $parsingDispatcher): mixed;
}
