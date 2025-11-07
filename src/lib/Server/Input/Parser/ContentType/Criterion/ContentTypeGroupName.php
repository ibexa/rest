<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Input\Parser\ContentType\Criterion;

use Ibexa\Contracts\Core\Repository\Values\ContentType\Query\Criterion\ContentTypeGroupName as ContentTypeGroupNameCriterion;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

final class ContentTypeGroupName extends BaseParser implements ContentTypeCriterionInterface
{
    private const string GROUP_NAME = 'ContentTypeGroupNameCriterion';

    /**
     * @param array<mixed> $data
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\Parser
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): ContentTypeGroupNameCriterion
    {
        if (!array_key_exists(self::GROUP_NAME, $data)) {
            throw new Exceptions\Parser('Invalid <' . self::GROUP_NAME . '> format');
        }

        $ids = $data[self::GROUP_NAME];

        return new ContentTypeGroupNameCriterion($ids);
    }

    public function getName(): string
    {
        return self::GROUP_NAME;
    }
}
