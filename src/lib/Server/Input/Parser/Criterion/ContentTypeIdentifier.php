<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser\Criterion;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ContentTypeId;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ContentTypeId as ContentTypeIdCriterion;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

/**
 * Parser for ViewInput.
 */
class ContentTypeIdentifier extends BaseParser
{
    protected ContentTypeService $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Parses input structure to a Criterion object.
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\Parser
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): ContentTypeId
    {
        if (!array_key_exists('ContentTypeIdentifierCriterion', $data)) {
            throw new Exceptions\Parser('Invalid <ContentTypeIdCriterion> format');
        }
        if (!is_array($data['ContentTypeIdentifierCriterion'])) {
            $data['ContentTypeIdentifierCriterion'] = [$data['ContentTypeIdentifierCriterion']];
        }

        return new ContentTypeIdCriterion(
            array_map(
                function ($contentTypeIdentifier) {
                    return $this->contentTypeService->loadContentTypeByIdentifier($contentTypeIdentifier)->id;
                },
                $data['ContentTypeIdentifierCriterion']
            )
        );
    }
}
