<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Input\Parser\Aggregation\Field;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\AbstractRangeAggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\Field\TimeRangeAggregation;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Server\Input\Parser\Aggregation\AbstractRangeAggregationParser;

final class TimeRangeAggregationParser extends AbstractRangeAggregationParser
{
    protected function getAggregationName(): string
    {
        return 'TimeRangeAggregation';
    }

    protected function parseAggregation(array $data, ParsingDispatcher $parsingDispatcher): AbstractRangeAggregation
    {
        if (!array_key_exists('contentTypeIdentifier', $data)) {
            throw new Exceptions\Parser("Missing 'contentTypeIdentifier' element for {$this->getAggregationName()}");
        }

        if (!array_key_exists('fieldDefinitionIdentifier', $data)) {
            throw new Exceptions\Parser("Missing 'fieldDefinitionIdentifier' element for {$this->getAggregationName()}.");
        }

        return new TimeRangeAggregation(
            $data['name'],
            $data['contentTypeIdentifier'],
            $data['fieldDefinitionIdentifier'],
            $this->dispatchRanges(
                $parsingDispatcher,
                $data['ranges'],
                'application/vnd.ibexa.api.internal.aggregation.range.IntRange'
            )
        );
    }
}

class_alias(TimeRangeAggregationParser::class, 'EzSystems\EzPlatformRest\Server\Input\Parser\Aggregation\Field\TimeRangeAggregationParser');
