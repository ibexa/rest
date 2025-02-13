<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Input\Parser\Criterion;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Location\IsBookmarked as IsBookmarkedCriterion;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;
use Ibexa\Rest\Input\ParserTools;

final class IsBookmarked extends BaseParser
{
    public const IS_BOOKMARKED_CRITERION = 'IsBookmarkedCriterion';

    private ParserTools $parserTools;

    public function __construct(ParserTools $parserTools)
    {
        $this->parserTools = $parserTools;
    }

    public function parse(array $data, ParsingDispatcher $parsingDispatcher): IsBookmarkedCriterion
    {
        if (!array_key_exists(self::IS_BOOKMARKED_CRITERION, $data)) {
            throw new Parser('Invalid <IsBookmarkedCriterion> format');
        }

        return new IsBookmarkedCriterion(
            $this->parserTools->parseBooleanValue($data[self::IS_BOOKMARKED_CRITERION])
        );
    }
}
