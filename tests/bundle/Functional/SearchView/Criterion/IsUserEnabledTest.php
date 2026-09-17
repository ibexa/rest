<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\Rest\Functional\SearchView\Criterion;

use Ibexa\Tests\Bundle\Rest\Functional\SearchView\SearchCriterionTestCase;

final class IsUserEnabledTest extends SearchCriterionTestCase
{
    public static function getCriteriaPayloads(): iterable
    {
        return [
            'is user enabled' => [
                'json',
                self::buildJsonCriterionQuery('"IsUserEnabledCriterion": true'),
                2,
            ],
            'is user disabled' => [
                'json',
                self::buildJsonCriterionQuery('"IsUserEnabledCriterion": false'),
                0,
            ],
        ];
    }
}
