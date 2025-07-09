<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser\SortClause;

use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\Field as FieldSortClause;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

class Field extends BaseParser
{
    /**
     * Parse input structure for Field sort clause.
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): FieldSortClause
    {
        if (!isset($data['Field'])) {
            throw new Exceptions\Parser("The <Field> Sort Clause doesn't exist in the input structure");
        }

        if (!is_array($data['Field'])) {
            throw new Exceptions\Parser('The <Field> Sort Clause has missing arguments: contentTypeIdentifier, fieldDefinitionIdentifier');
        }

        $data['Field'] = $this->normalizeData($data['Field']);

        $direction = isset($data['Field']['direction']) ? $data['Field']['direction'] : null;

        if (!in_array($direction, [Query::SORT_ASC, Query::SORT_DESC])) {
            throw new Exceptions\Parser('Invalid direction format in <Field> sort clause');
        }

        if (isset($data['Field']['identifier'])) {
            if (false === strpos($data['Field']['identifier'], '/')) {
                throw new Exceptions\Parser('<Field> Sort Clause parameter "identifier" value has to be in "contentTypeIdentifier/fieldDefinitionIdentifier" format');
            }

            list($contentTypeIdentifier, $fieldDefinitionIdentifier) = explode('/', $data['Field']['identifier'], 2);
        } else {
            if (!isset($data['Field']['contentTypeIdentifier'])) {
                throw new Exceptions\Parser('<Field> Sort Clause has missing parameter "contentTypeIdentifier"');
            }
            if (!isset($data['Field']['fieldDefinitionIdentifier'])) {
                throw new Exceptions\Parser('<Field> Sort Clause has missing parameter "fieldDefinitionIdentifier"');
            }

            $contentTypeIdentifier = $data['Field']['contentTypeIdentifier'];
            $fieldDefinitionIdentifier = $data['Field']['fieldDefinitionIdentifier'];
        }

        return new FieldSortClause($contentTypeIdentifier, $fieldDefinitionIdentifier, $direction);
    }

    /**
     * Normalize passed Field Sort Clause data by making both xml and json parameters to have same names (by dropping
     * xml "_" prefix and changing "#text" xml attribute to "direction").
     */
    private function normalizeData(array $data): array
    {
        $normalizedData = [];

        foreach ($data as $key => $value) {
            if ('#text' === $key) {
                $key = 'direction';
            }

            $normalizedData[trim($key, '_')] = $value;
        }

        return $normalizedData;
    }
}
