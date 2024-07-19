<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Contracts\Rest\Input\Parser\Query\SortClause;

use Traversable;

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
     * @return \Traversable<TSortClause>
     */
    public function processSortClauses(array $sortClauseData): Traversable;
}
