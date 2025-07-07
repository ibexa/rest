<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser\Criterion;

use Ibexa\Rest\Server\Input\Parser\Criterion;

/**
 * Parser for LogicalOperator Criterion.
 */
abstract class LogicalOperator extends Criterion
{
    protected function getFlattenedCriteriaData(array $criteriaByType): array
    {
        $criteria = [];
        foreach ($criteriaByType as $type => $criterion) {
            if (!is_array($criterion) || !$this->isZeroBasedArray($criterion)) {
                $criterion = [$criterion];
            }

            foreach ($criterion as $criterionElement) {
                $criteria[] = [
                    'type' => $type,
                    'data' => $criterionElement,
                ];
            }
        }

        return $criteria;
    }

    /**
     * Checks if the given $value is zero based.
     */
    protected function isZeroBasedArray(array $value): bool
    {
        reset($value);

        return empty($value) || key($value) === 0;
    }
}
