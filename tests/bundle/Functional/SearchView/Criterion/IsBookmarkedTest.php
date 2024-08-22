<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\Bundle\Rest\Functional\SearchView\Criterion;

use Ibexa\Tests\Bundle\Rest\Functional\SearchView\SearchCriterionTestCase;

final class IsBookmarkedTest extends SearchCriterionTestCase
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
        yield 'Bookmarked locations' => [
            'json',
            $this->buildJsonCriterionQuery('"IsBookmarkedCriterion": true'),
            4,
        ];

        yield 'Not bookmarked locations' => [
            'json',
            $this->buildJsonCriterionQuery('"IsBookmarkedCriterion": false'),
            19,
        ];
    }
}
