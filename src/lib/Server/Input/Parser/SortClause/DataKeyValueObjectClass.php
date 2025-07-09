<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser\SortClause;

use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

class DataKeyValueObjectClass extends BaseParser
{
    /**
     * Data key, corresponding to the $valueObjectClass class.
     * Example: 'DatePublished'.
     */
    protected string $dataKey;

    /**
     * Value object class, corresponding to the $dataKey.
     * Example: 'Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\DatePublished'.
     */
    protected string $valueObjectClass;

    public function __construct(string $dataKey, string $valueObjectClass)
    {
        $this->dataKey = $dataKey;
        $this->valueObjectClass = $valueObjectClass;
    }

    public function parse(array $data, ParsingDispatcher $parsingDispatcher): object
    {
        if (!class_exists($this->valueObjectClass)) {
            throw new Exceptions\Parser("Value object class <{$this->valueObjectClass}> is not defined");
        }

        if (!array_key_exists($this->dataKey, $data)) {
            throw new Exceptions\Parser("The <{$this->dataKey}> Sort Clause doesn't exist in the input structure");
        }

        $direction = $data[$this->dataKey];

        if (!in_array($direction, [Query::SORT_ASC, Query::SORT_DESC])) {
            throw new Exceptions\Parser("Invalid direction format in the <{$this->dataKey}> Sort Clause");
        }

        return new $this->valueObjectClass($direction);
    }

    public function getDataKey(): string
    {
        return $this->dataKey;
    }
}
