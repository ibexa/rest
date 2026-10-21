<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Input\Parser\ContentType\SortClause;

use Ibexa\Rest\Server\Input\Parser\SortClause\DataKeyValueObjectClass;

final class ContentTypeSortClausesRegistry
{
    /** @var iterable<DataKeyValueObjectClass> */
    private iterable $sortClauses;

    /**
     * @param iterable<DataKeyValueObjectClass> $sortClauses
     */
    public function __construct(iterable $sortClauses)
    {
        $this->sortClauses = $sortClauses;
    }

    /**
     * @return iterable<DataKeyValueObjectClass>
     */
    public function getSortClauses(): iterable
    {
        return $this->sortClauses;
    }
}
