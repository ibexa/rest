<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\Rest\Functional\SearchView\Criterion;

use Ibexa\Tests\Bundle\Rest\Functional\SearchView\SearchCriterionTestCase;

/**
 * @covers \Ibexa\Rest\Server\Input\Parser\Criterion\ContentName
 */
final class ContentNameTest extends SearchCriterionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createFolder('foo', '/api/ibexa/v2/content/locations/1/2');
        $this->createFolder('foobar', '/api/ibexa/v2/content/locations/1/2');
    }

    /**
     * @return iterable<array{
     *     string,
     *     string,
     *     int,
     * }>
     */
    public function getCriteriaPayloads(): iterable
    {
        yield 'Return content items that contain "foo" in name' => [
            'json',
            $this->buildJsonCriterionQuery('"ContentNameCriterion": "foo*"'),
            2,
        ];

        yield 'No content items found with article in name' => [
            'json',
            $this->buildJsonCriterionQuery('"ContentNameCriterion": "*article*"'),
            0,
        ];
    }
}
