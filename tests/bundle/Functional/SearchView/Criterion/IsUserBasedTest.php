<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\Rest\Functional\SearchView\Criterion;

use Ibexa\Tests\Bundle\Rest\Functional\SearchView\SearchCriterionTestCase;

final class IsUserBasedTest extends SearchCriterionTestCase
{
    public static function getCriteriaPayloads(): iterable
    {
        return [
            'is user based' => [
                'json',
                self::buildJsonCriterionQuery('"IsUserBasedCriterion": true'),
                2,
            ],
            'is not user based' => [
                'json',
                self::buildJsonCriterionQuery('"IsUserBasedCriterion": false'),
                14,
            ],
        ];
    }
}
