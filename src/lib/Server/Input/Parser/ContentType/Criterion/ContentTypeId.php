<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Input\Parser\ContentType\Criterion;

use Ibexa\Contracts\Core\Repository\Values\ContentType\Query\Criterion\ContentTypeId as ContentTypeIdCriterion;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

final class ContentTypeId extends BaseParser implements ContentTypeCriterionInterface
{
    private const string ID_CRITERION = 'ContentTypeIdCriterion';

    /**
     * @param array<mixed> $data
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\Parser
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): ContentTypeIdCriterion
    {
        if (!array_key_exists(self::ID_CRITERION, $data)) {
            throw new Exceptions\Parser('Invalid <' . self::ID_CRITERION . '> format');
        }

        $ids = $data[self::ID_CRITERION];

        return new ContentTypeIdCriterion($ids);
    }

    public function getName(): string
    {
        return self::ID_CRITERION;
    }
}
