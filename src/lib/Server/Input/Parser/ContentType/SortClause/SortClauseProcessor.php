<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Input\Parser\ContentType\SortClause;

use Ibexa\Contracts\Rest\Input\Parser\Query\SortClause\BaseSortClauseProcessor;

/**
 * @internal
 *
 * @template TSortClause
 *
 * @phpstan-type TSortClauseProcessor \Ibexa\Contracts\Rest\Input\Parser\Query\SortClause\SortClauseProcessorInterface<
 *     TSortClause
 * >
 *
 * @extends \Ibexa\Contracts\Rest\Input\Parser\Query\SortClause\BaseSortClauseProcessor<
 *     TSortClause
 * >
 */
final class SortClauseProcessor extends BaseSortClauseProcessor
{
    protected function getMediaTypePrefix(): string
    {
        return 'application/vnd.ibexa.api.internal.sortclause';
    }

    protected function getParserInvalidSortClauseMessage(string $sortClauseName): string
    {
        return "Invalid Sort Clause <$sortClauseName> in <AND>";
    }
}
