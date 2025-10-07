<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Input\Parser\ContentType\SortClause;

final class ContentTypeSortClausesRegistry
{
    /** @var iterable<\Ibexa\Rest\Server\Input\Parser\SortClause\DataKeyValueObjectClass> */
    private iterable $sortClauses;

    /**
     * @param iterable<\Ibexa\Rest\Server\Input\Parser\SortClause\DataKeyValueObjectClass> $sortClauses
     */
    public function __construct(iterable $sortClauses)
    {
        $this->sortClauses = $sortClauses;
    }

    /**
     * @return iterable<\Ibexa\Rest\Server\Input\Parser\SortClause\DataKeyValueObjectClass>
     */
    public function getSortClauses(): iterable
    {
        return $this->sortClauses;
    }
}
