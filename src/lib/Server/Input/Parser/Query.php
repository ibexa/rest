<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\Values\Content\Query as ContentQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion as CriterionValue;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Server\Input\Parser\Criterion as CriterionParser;

/**
 * Content/Location Query Parser.
 */
abstract class Query extends CriterionParser
{
    /**
     * @throws \Ibexa\Contracts\Rest\Exceptions\Parser
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): ContentQuery
    {
        $query = $this->buildQuery();

        if (array_key_exists('Filter', $data) && is_array($data['Filter'])) {
            $query->filter = $this->processCriteriaArray($data['Filter'], $parsingDispatcher);
        }

        if (array_key_exists('Query', $data) && is_array($data['Query'])) {
            $query->query = $this->processCriteriaArray($data['Query'], $parsingDispatcher);
        }

        // limit
        if (array_key_exists('limit', $data)) {
            $query->limit = (int)$data['limit'];
        }

        // offset
        if (array_key_exists('offset', $data)) {
            $query->offset = (int)$data['offset'];
        }

        // SortClauses
        // -- [SortClauseName: direction|data]
        if (array_key_exists('SortClauses', $data)) {
            $query->sortClauses = $this->processSortClauses($data['SortClauses'], $parsingDispatcher);
        }

        if (array_key_exists('Aggregations', $data)) {
            foreach ($data['Aggregations'] as $aggregation) {
                $aggregationName = array_key_first($aggregation);
                $aggregationData = $aggregation[$aggregationName];

                $query->aggregations[] = $this->dispatchAggregation(
                    $aggregationName,
                    $aggregationData,
                    $parsingDispatcher
                );
            }
        }

        return $query;
    }

    /**
     * Builds and returns the Query (Location or Content object).
     */
    abstract protected function buildQuery(): ContentQuery;

    private function processCriteriaArray(array $criteriaArray, ParsingDispatcher $parsingDispatcher): ?CriterionInterface
    {
        if (count($criteriaArray) === 0) {
            return null;
        }

        $criteria = [];
        foreach ($criteriaArray as $criterionName => $criterionData) {
            $criteria[] = $this->dispatchCriterion($criterionName, $criterionData, $parsingDispatcher);
        }

        return (count($criteria) === 1) ? $criteria[0] : new CriterionValue\LogicalAnd($criteria);
    }

    /**
     * Handles SortClause data.
     */
    private function processSortClauses(array $sortClausesArray, ParsingDispatcher $parsingDispatcher): array
    {
        $sortClauses = [];
        foreach ($sortClausesArray as $sortClauseName => $sortClauseData) {
            if (!is_array($sortClauseData) || !isset($sortClauseData[0])) {
                $sortClauseData = [$sortClauseData];
            }

            foreach ($sortClauseData as $data) {
                $sortClauses[] = $this->dispatchSortClause($sortClauseName, $data, $parsingDispatcher);
            }
        }

        return $sortClauses;
    }
}
