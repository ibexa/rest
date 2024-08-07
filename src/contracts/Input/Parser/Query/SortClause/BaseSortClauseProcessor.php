<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Contracts\Rest\Input\Parser\Query\SortClause;

use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;

/**
 * @template TSortClause
 *
 * @internal
 */
abstract class BaseSortClauseProcessor implements SortClauseProcessorInterface
{
    private ParsingDispatcher $parsingDispatcher;

    public function __construct(ParsingDispatcher $parsingDispatcher)
    {
        $this->parsingDispatcher = $parsingDispatcher;
    }

    public function processSortClauses(array $sortClauseData): iterable
    {
        if (empty($sortClauseData)) {
            yield from [];
        }

        foreach ($sortClauseData as $sortClauseName => $direction) {
            $mediaType = $this->getSortClauseMediaType($sortClauseName);

            try {
                yield $this->parsingDispatcher->parse([$sortClauseName => $direction], $mediaType);
            } catch (Exceptions\Parser $e) {
                throw new Exceptions\Parser($this->getParserInvalidSortClauseMessage($sortClauseName), 0, $e);
            }
        }
    }

    abstract protected function getMediaTypePrefix(): string;

    abstract protected function getParserInvalidSortClauseMessage(string $sortClauseName): string;
    
    private function getSortClauseMediaType(string $sortClauseName): string
    {
        $mediaTypePrefix = $this->getMediaTypePrefix();
        if ('.' !== substr($mediaTypePrefix, strlen($mediaTypePrefix) - 1)) {
            $mediaTypePrefix .= '.';
        }

        return $mediaTypePrefix . $sortClauseName;
    }
}
