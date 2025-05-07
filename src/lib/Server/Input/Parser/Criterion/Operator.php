<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser\Criterion;

use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

/**
 * Parser for Operator Criterion.
 */
class Operator extends BaseParser
{
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): never
    {
        throw new \Exception('@todo implement');
    }
}
