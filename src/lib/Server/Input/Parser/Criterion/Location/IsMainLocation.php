<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Input\Parser\Criterion\Location;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Location\IsMainLocation as IsMainLocationCriterion;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

final class IsMainLocation extends BaseParser
{
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): IsMainLocationCriterion
    {
        if (!array_key_exists('IsMainLocation', $data)) {
            throw new Exceptions\Parser('Invalid <IsMainLocation> format');
        }

        return new IsMainLocationCriterion($data['IsMainLocation']);
    }
}
