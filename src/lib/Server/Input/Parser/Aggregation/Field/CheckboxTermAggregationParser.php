<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Input\Parser\Aggregation\Field;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\AbstractTermAggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\Field\CheckboxTermAggregation;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Server\Input\Parser\Aggregation\AbstractTermAggregationParser;

final class CheckboxTermAggregationParser extends AbstractTermAggregationParser
{
    protected function getAggregationName(): string
    {
        return 'CheckboxTermAggregation';
    }

    protected function parseAggregation(array $data, ParsingDispatcher $parsingDispatcher): AbstractTermAggregation
    {
        if (!array_key_exists('contentTypeIdentifier', $data)) {
            throw new Exceptions\Parser("Missing 'contentTypeIdentifier' element for {$this->getAggregationName()}");
        }

        if (!array_key_exists('fieldDefinitionIdentifier', $data)) {
            throw new Exceptions\Parser("Missing 'fieldDefinitionIdentifier' element for {$this->getAggregationName()}.");
        }

        return new CheckboxTermAggregation(
            $data['name'],
            $data['contentTypeIdentifier'],
            $data['fieldDefinitionIdentifier']
        );
    }
}
