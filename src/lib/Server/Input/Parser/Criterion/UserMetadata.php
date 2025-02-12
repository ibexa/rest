<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser\Criterion;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\UserMetadata as UserMetadataCriterion;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

/**
 * Parser for ViewInput.
 */
class UserMetadata extends BaseParser
{
    /**
     * @phpstan-param array{UserMetadataCriterion: array{Target: string, Value: int|string|array}} $data
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\Parser
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): UserMetadataCriterion
    {
        if (!isset($data['UserMetadataCriterion'])) {
            throw new Exceptions\Parser('Invalid <UserMetadataCriterion> format');
        }

        if (!isset($data['UserMetadataCriterion']['Target'])) {
            throw new Exceptions\Parser('Invalid <Target> format');
        }

        $target = $data['UserMetadataCriterion']['Target'];

        if (!isset($data['UserMetadataCriterion']['Value'])) {
            throw new Exceptions\Parser('Invalid <Value> format');
        }

        if (!in_array(gettype($data['UserMetadataCriterion']['Value']), ['integer', 'string', 'array'])) {
            throw new Exceptions\Parser('Invalid <Value> format');
        }

        $value = is_array($data['UserMetadataCriterion']['Value'])
            ? $data['UserMetadataCriterion']['Value']
            : explode(',', (string)$data['UserMetadataCriterion']['Value']);

        return new UserMetadataCriterion($target, null, $value);
    }
}
