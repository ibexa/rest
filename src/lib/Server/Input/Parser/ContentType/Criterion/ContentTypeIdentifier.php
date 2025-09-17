<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Input\Parser\ContentType\Criterion;

use Ibexa\Contracts\Core\Repository\Values\ContentType\Query\Criterion\ContentTypeIdentifier as ContentTypeIdentifierCriterion;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

final class ContentTypeIdentifier extends BaseParser implements ContentTypeCriterionInterface
{
    private const IDENTIFIER_CRITERION = 'ContentTypeIdentifierCriterion';

    /**
     * @param array<mixed> $data
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\Parser
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): ContentTypeIdentifierCriterion
    {
        if (!array_key_exists(self::IDENTIFIER_CRITERION, $data)) {
            throw new Exceptions\Parser('Invalid <' . self::IDENTIFIER_CRITERION . '> format');
        }

        $ids = $data[self::IDENTIFIER_CRITERION];

        return new ContentTypeIdentifierCriterion($ids);
    }

    public function getName(): string
    {
        return self::IDENTIFIER_CRITERION;
    }
}
