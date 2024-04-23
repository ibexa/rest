<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\Rest\Functional\SearchView\Criterion;

use Ibexa\Tests\Bundle\Rest\Functional\SearchView\SearchCriterionTestCase;

final class IsContainerTest extends SearchCriterionTestCase
{
    /**
     * @phpstan-return iterable<
     *     string,
     *     array{
     *         string,
     *         string,
     *         int,
     *     },
     * >
     */
    public function getCriteriaPayloads(): iterable
    {
        return [
            'is container' => [
                'json',
                $this->buildJsonCriterionQuery('"IsContainerCriterion": true'),
                10,
            ],
            'is not container' => [
                'json',
                $this->buildJsonCriterionQuery('"IsContainerCriterion": false'),
                2,
            ],
        ];
    }
}
