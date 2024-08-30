<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Rest\Input\Parser\Query\SortClause;

/**
 * @template TSortClause
 *
 * @internal
 */
interface SortClauseProcessorInterface
{
    /**
     * @param array<string, string> $sortClauseData
     *
     * @return iterable<TSortClause>
     */
    public function processSortClauses(array $sortClauseData): iterable;
}
