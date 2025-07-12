<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\Rest\Functional\SearchView\Criterion;

use Ibexa\Tests\Bundle\Rest\Functional\SearchView\SearchCriterionTestCase;

final class ObjectStateIdentifierTest extends SearchCriterionTestCase
{
    public function getCriteriaPayloads(): iterable
    {
        return [
            'identifier with target group' => [
                'json',
                $this->buildJsonCriterionQuery('"ObjectStateIdentifierCriterion": {"value": "not_locked", "target": "ibexa_lock"}'),
                16,
            ],
            'identifier without target group' => [
                'json',
                $this->buildJsonCriterionQuery('"ObjectStateIdentifierCriterion": {"value": "not_locked", "target": null}'),
                16,
            ],
        ];
    }
}
