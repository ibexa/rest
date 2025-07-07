<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser\Criterion;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LogicalAnd as LogicalAndCriterion;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;

/**
 * Parser for LogicalAnd Criterion.
 */
class LogicalAnd extends LogicalOperator
{
    public const string TAG_NAME = 'AND';

    /**
     * @throws \Ibexa\Contracts\Rest\Exceptions\Parser
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): LogicalAndCriterion
    {
        if (!array_key_exists(static::TAG_NAME, $data) || !is_array($data[static::TAG_NAME])) {
            throw new Exceptions\Parser('Invalid <' . static::TAG_NAME . '> format');
        }

        $criteria = [];

        $flattenedCriteriaElements = $this->getFlattenedCriteriaData($data[static::TAG_NAME]);
        foreach ($flattenedCriteriaElements as $criterionElement) {
            $criteria[] = $this->dispatchCriterion(
                $criterionElement['type'],
                $criterionElement['data'],
                $parsingDispatcher
            );
        }

        return new LogicalAndCriterion($criteria);
    }
}
