<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

/**
 * Parser for ViewInput.
 */
abstract class Criterion extends BaseParser
{
    /**
     * @var string[]
     */
    protected static array $criterionIdMap = [
        'AND' => 'LogicalAnd',
        'OR' => 'LogicalOr',
        'NOT' => 'LogicalNot',
    ];

    /**
     * Dispatches parsing of a criterion name + data to its own parser.
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\Parser
     */
    public function dispatchCriterion(
        string $criterionName,
        mixed $criterionData,
        ParsingDispatcher $parsingDispatcher
    ): CriterionInterface {
        $mediaType = $this->getCriterionMediaType($criterionName);
        try {
            return $parsingDispatcher->parse([$criterionName => $criterionData], $mediaType);
        } catch (Exceptions\Parser $e) {
            throw new Exceptions\Parser("Invalid Criterion id <$criterionName> in <AND>", 0, $e);
        }
    }

    /**
     * Dispatches parsing of an aggregation name + data to its own parser.
     */
    public function dispatchAggregation(
        string $aggregationName,
        array $aggregationData,
        ParsingDispatcher $parsingDispatcher
    ): Aggregation {
        return $parsingDispatcher->parse(
            [
                $aggregationName => $aggregationData,
            ],
            $this->getAggregationMediaType($aggregationName)
        );
    }

    /**
     * Dispatches parsing of a sort clause name + direction to its own parser.
     *
     * @param array<string, string>|string $direction
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\Parser
     */
    public function dispatchSortClause(
        string $sortClauseName,
        array|string $direction,
        ParsingDispatcher $parsingDispatcher
    ): SortClause {
        $mediaType = $this->getSortClauseMediaType($sortClauseName);

        return $parsingDispatcher->parse([$sortClauseName => $direction], $mediaType);
    }

    protected function getCriterionMediaType(string $criterionName): string
    {
        $criterionName = str_replace('Criterion', '', $criterionName);
        if (isset(self::$criterionIdMap[$criterionName])) {
            $criterionName = self::$criterionIdMap[$criterionName];
        }

        return 'application/vnd.ibexa.api.internal.criterion.' . $criterionName;
    }

    protected function getSortClauseMediaType(string $sortClauseName): string
    {
        return 'application/vnd.ibexa.api.internal.sortclause.' . $sortClauseName;
    }

    protected function getAggregationMediaType(string $aggregationName): string
    {
        return 'application/vnd.ibexa.api.internal.aggregation.' . $aggregationName;
    }
}
