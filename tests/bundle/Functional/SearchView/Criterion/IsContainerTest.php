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
    public function getCriteriaPayloads(): iterable
    {
        return [
            'is container' => [
                'json',
                $this->buildJsonCriterionQuery('"IsContainer": true'),
                11,
            ],
            'is not container' => [
                'json',
                $this->buildJsonCriterionQuery('"IsContainer": false'),
                2,
            ],
        ];
    }
}
