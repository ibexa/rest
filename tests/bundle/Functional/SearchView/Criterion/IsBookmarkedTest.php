<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Bundle\Rest\Functional\SearchView\Criterion;

use Ibexa\Tests\Bundle\Rest\Functional\SearchView\SearchCriterionTestCase;

final class IsBookmarkedTest extends SearchCriterionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->addMediaFolderToBookmarks();
    }

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
            1,
        ];

        yield 'Not bookmarked locations' => [
            'json',
            $this->buildJsonCriterionQuery('"IsBookmarkedCriterion": false'),
            15, // <- This can differ between DXP versions.
        ];
    }

    private function addMediaFolderToBookmarks(): void
    {
        $request = $this->createHttpRequest(
            'POST',
            '/api/ibexa/v2/bookmark/43'
        );

        $this->sendHttpRequest($request);
    }
}
