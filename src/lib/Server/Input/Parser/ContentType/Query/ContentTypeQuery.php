<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Input\Parser\ContentType\Query;

use Ibexa\Contracts\Core\Repository\Values\ContentType\Query\ContentTypeQuery as ContentTypeQueryValueObject;
use Ibexa\Contracts\Core\Repository\Values\ContentType\Query\Criterion\LogicalAnd;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Contracts\Rest\Input\Parser\Query\Criterion\CriterionProcessorInterface;
use Ibexa\Contracts\Rest\Input\Parser\Query\SortClause\SortClauseProcessorInterface;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use function Ibexa\PolyfillPhp82\iterator_to_array;
use Ibexa\Rest\Input\BaseParser;

/**
 * @phpstan-import-type TCriterionProcessor from \Ibexa\Rest\Server\Input\Parser\ContentType\Criterion\CriterionProcessor
 * @phpstan-import-type TSortClauseProcessor from \Ibexa\Rest\Server\Input\Parser\ContentType\SortClause\SortClauseProcessor
 *
 * @template CR of \Ibexa\Contracts\Core\Repository\Values\ContentType\Query\CriterionInterface
 * @template SC of \Ibexa\Contracts\Core\Repository\Values\ContentType\Query\SortClause
 */
final class ContentTypeQuery extends BaseParser
{
    private const QUERY = 'Query';
    private const SORT_CLAUSES = 'SortClauses';
    private const AGGREGATIONS = 'Aggregations';

    private CriterionProcessorInterface $criterionProcessor;

    private SortClauseProcessorInterface $sortClauseProcessor;

    public function __construct(
        CriterionProcessorInterface $criterionProcessor,
        SortClauseProcessorInterface $sortClauseProcessor
    ) {
        $this->criterionProcessor = $criterionProcessor;
        $this->sortClauseProcessor = $sortClauseProcessor;
    }

    /**
     * @return list<string>
     */
    private function getAllowedKeys(): array
    {
        return [
            self::QUERY,
            self::SORT_CLAUSES,
            self::AGGREGATIONS,
        ];
    }

    /**
     * @param array<mixed> $data
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidCriterionArgumentException
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): object
    {
        if (!empty($redundantKeys = $this->checkRedundantKeys(array_keys($data)))) {
            throw new Parser(
                sprintf(
                    'The following properties are redundant: %s.',
                    implode(', ', $redundantKeys)
                )
            );
        }

        $query = $this->buildQuery($data);

        if (array_key_exists('limit', $data)) {
            $query->setLimit((int)$data['limit']);
        }

        if (array_key_exists('offset', $data)) {
            $query->setOffset((int)$data['offset']);
        }

        return $query;
    }

    /**
     * @param array<mixed> $data
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidCriterionArgumentException
     */
    private function buildQuery(array $data): ContentTypeQueryValueObject
    {
        $query = new ContentTypeQueryValueObject();

        if (array_key_exists(self::QUERY, $data) && is_array($data[self::QUERY])) {
            $criteria = $this->processCriteriaArray($data[self::QUERY]);
            if (count($criteria) > 0) {
                /** @var list<\Ibexa\Contracts\Core\Repository\Values\ContentType\Query\CriterionInterface> $criteria */
                $query->setCriterion(new LogicalAnd($criteria));
            }
        }

        if (array_key_exists(self::SORT_CLAUSES, $data)) {
            $sortClauses = $this->processSortClauses($data[self::SORT_CLAUSES]);
            foreach ($sortClauses as $sortClause) {
                $query->addSortClause($sortClause);
            }
        }

        return $query;
    }

    /**
     * @param array<string, array<mixed>> $criteriaArray
     *
     * @phpstan-return array<CR>
     */
    private function processCriteriaArray(array $criteriaArray): array
    {
        $processedCriteria = $this->criterionProcessor->processCriteria($criteriaArray);

        return iterator_to_array($processedCriteria);
    }

    /**
     * @param array<string, string> $sortClausesArray
     *
     * @phpstan-return array<SC>
     */
    private function processSortClauses(array $sortClausesArray): array
    {
        $processedSortClauses = $this->sortClauseProcessor->processSortClauses($sortClausesArray);

        return iterator_to_array($processedSortClauses);
    }

    /**
     * @param list<string> $providedKeys
     *
     * @return array<int<0, max>, string>
     */
    private function checkRedundantKeys(array $providedKeys): array
    {
        $allowedKeys = array_merge(
            $this->getAllowedKeys(),
            ['limit', 'offset']
        );

        return array_diff($providedKeys, $allowedKeys);
    }
}
