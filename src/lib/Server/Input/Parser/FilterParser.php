<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion as CriterionValue;
use Ibexa\Contracts\Core\Repository\Values\Filter\Filter;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

/**
 * @internal
 */
final class FilterParser extends BaseParser
{
    /**
     * Parses input structure to a Query.
     *
     * @param array<mixed> $data
     * @param \Ibexa\Contracts\Rest\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\Parser
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Filter\Filter
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): Filter
    {
        $filter = new Filter();
        if (array_key_exists('criteria', $data) && is_array($data['criteria'])) {
            $filter->andWithCriterion($this->processCriteriaArray($data['criteria'], $parsingDispatcher));
        }

        // limit
        if (array_key_exists('limit', $data)) {
            $filter->withLimit((int)$data['limit']);
        }

        // offset
        if (array_key_exists('offset', $data)) {
            $filter->withOffset((int)$data['offset']);
        }

        return $filter;
    }

    /**
     * @param array<string, array<mixed>> $criteriaArray
     * @param \Ibexa\Contracts\Rest\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion|null A criterion, or a LogicalAnd with a set of Criterion, or null if an empty array was given
     */
    private function processCriteriaArray(array $criteriaArray, ParsingDispatcher $parsingDispatcher)
    {
        if (count($criteriaArray) === 0) {
            return null;
        }

        $criteria = [];
        foreach ($criteriaArray as $criteriaSpec) {
            $criterionName = $criteriaSpec['type'];
            $criterionData = $criteriaSpec['value'];

            $criteria[] = $this->dispatchCriterion($criterionName, $criterionData, $parsingDispatcher);
        }

        return (count($criteria) === 1) ? $criteria[0] : new CriterionValue\LogicalAnd($criteria);
    }

    /**
     * Dispatches parsing of a criterion name + data to its own parser.
     *
     * @param string $criterionName
     * @param mixed $criterionData
     * @param \Ibexa\Contracts\Rest\Input\ParsingDispatcher $parsingDispatcher
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\Parser
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion
     */
    public function dispatchCriterion($criterionName, $criterionData, ParsingDispatcher $parsingDispatcher)
    {
        $mediaType = $this->getCriterionMediaType($criterionName);
        try {
            return $parsingDispatcher->parse([$criterionName => $criterionData], $mediaType);
        } catch (Exceptions\Parser $e) {
            throw new Exceptions\Parser("Invalid Criterion id <$criterionName> in <AND>", 0, $e);
        }
    }

    protected function getCriterionMediaType(string $criterionName): string
    {
        return 'application/vnd.ibexa.api.internal.criterion.' . $criterionName;
    }
}
