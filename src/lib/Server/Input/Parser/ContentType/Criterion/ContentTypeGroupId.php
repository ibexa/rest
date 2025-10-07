<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Input\Parser\ContentType\Criterion;

use Ibexa\Contracts\Core\Repository\Values\ContentType\Query\Criterion\ContentTypeGroupId as ContentTypeGroupIdCriterion;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

final class ContentTypeGroupId extends BaseParser implements ContentTypeCriterionInterface
{
    private const GROUP_ID = 'ContentTypeGroupIdCriterion';

    /**
     * @param array<mixed> $data
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\Parser
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): ContentTypeGroupIdCriterion
    {
        if (!array_key_exists(self::GROUP_ID, $data)) {
            throw new Exceptions\Parser('Invalid <' . self::GROUP_ID . '> format');
        }

        $ids = $data[self::GROUP_ID];

        return new ContentTypeGroupIdCriterion($ids);
    }

    public function getName(): string
    {
        return self::GROUP_ID;
    }
}
