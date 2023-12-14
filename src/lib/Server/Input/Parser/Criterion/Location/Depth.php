<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Input\Parser\Criterion\Location;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Location\Depth as DepthCriterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

final class Depth extends BaseParser
{
    private const OPERATORS = [
        'IN' => Operator::IN,
        'EQ' => Operator::EQ,
        'GT' => Operator::GT,
        'GTE' => Operator::GTE,
        'LT' => Operator::LT,
        'LTE' => Operator::LTE,
        'BETWEEN' => Operator::BETWEEN,
    ];

    public function parse(array $data, ParsingDispatcher $parsingDispatcher): DepthCriterion
    {
        if (!array_key_exists('LocationDepth', $data)) {
            throw new Exceptions\Parser('Invalid <LocationDepth> format');
        }

        $criterionData = $data['LocationDepth'];
        if (!is_array($criterionData)) {
            throw new Exceptions\Parser('Invalid <LocationDepth> format');
        }

        if (!isset($criterionData['Value'])) {
            throw new Exceptions\Parser('Invalid <Value> format');
        }

        if (
            is_string($criterionData['Value'])
            && is_numeric($criterionData['Value'])
            && ((int)$criterionData['Value'] == $criterionData['Value'])
        ) {
            $criterionData['Value'] = (int)$criterionData['Value'];
        }

        if (!in_array(gettype($criterionData['Value']), ['integer', 'array'], true)) {
            throw new Exceptions\Parser('Invalid <Value> format');
        }

        $value = $criterionData['Value'];

        if (!isset($criterionData['Operator'])) {
            throw new Exceptions\Parser('Invalid <Operator> format');
        }

        $operator = $this->getOperator($criterionData['Operator']);

        return new DepthCriterion($operator, $value);
    }

    /**
     * Get operator for the given literal name.
     */
    private function getOperator(string $operatorName): string
    {
        $operatorName = strtoupper($operatorName);
        if (!isset(self::OPERATORS[$operatorName])) {
            throw new Exceptions\Parser(
                sprintf(
                    'Unexpected LocationDepth operator. Expected one of: %s',
                    implode(', ', array_keys(self::OPERATORS))
                )
            );
        }

        return self::OPERATORS[$operatorName];
    }
}
