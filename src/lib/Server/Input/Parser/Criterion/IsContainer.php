<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Input\Parser\Criterion;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\IsContainer as IsContainerCriterion;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;
use Ibexa\Rest\Input\ParserTools;

class IsContainer extends BaseParser
{
    /** @var \Ibexa\Rest\Input\ParserTools */
    protected $parserTools;

    public function __construct(ParserTools $parserTools)
    {
        $this->parserTools = $parserTools;
    }

    /**
     * @param array<mixed> $data
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): IsContainerCriterion
    {
        if (!array_key_exists('IsContainerCriterion', $data)) {
            throw new Exceptions\Parser('Invalid <IsContainer> format');
        }

        return new IsContainerCriterion($this->parserTools->parseBooleanValue($data['IsContainerCriterion']));
    }
}
