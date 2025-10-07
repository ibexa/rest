<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Input\Parser\ContentType\Criterion;

use Ibexa\Contracts\Core\Repository\Values\ContentType\Query\Criterion\IsSystem as IsSystemCriterion;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

final class IsSystem extends BaseParser implements ContentTypeCriterionInterface
{
    private const string IS_SYSTEM_CRITERION = 'IsSystemCriterion';

    /**
     * @param array<mixed> $data
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\Parser
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): IsSystemCriterion
    {
        if (!array_key_exists(self::IS_SYSTEM_CRITERION, $data)) {
            throw new Exceptions\Parser('Invalid <' . self::IS_SYSTEM_CRITERION . '> format');
        }

        $ids = $data[self::IS_SYSTEM_CRITERION];

        return new IsSystemCriterion($ids);
    }

    public function getName(): string
    {
        return self::IS_SYSTEM_CRITERION;
    }
}
