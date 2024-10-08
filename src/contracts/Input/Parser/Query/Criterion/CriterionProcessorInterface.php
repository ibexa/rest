<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Rest\Input\Parser\Query\Criterion;

/**
 * @template TCriterion
 *
 * @internal
 */
interface CriterionProcessorInterface
{
    /**
     * @param array<string, array<mixed>> $criteriaData
     *
     * @return iterable<TCriterion>
     */
    public function processCriteria(array $criteriaData): iterable;
}
