<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser\Criterion;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ContentTypeId;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ContentTypeId as ContentTypeIdCriterion;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

/**
 * Parser for ViewInput.
 */
class ContentTypeIdentifier extends BaseParser
{
    /**
     * Content type service.
     *
     * @var ContentTypeService
     */
    protected $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Parses input structure to a Criterion object.
     *
     * @param array $data
     * @param ParsingDispatcher $parsingDispatcher
     *
     * @throws Parser
     *
     * @return ContentTypeId
     */
    public function parse(
        array $data,
        ParsingDispatcher $parsingDispatcher
    ) {
        if (!array_key_exists('ContentTypeIdentifierCriterion', $data)) {
            throw new Parser('Invalid <ContentTypeIdCriterion> format');
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

class_alias(ContentTypeIdentifier::class, 'EzSystems\EzPlatformRest\Server\Input\Parser\Criterion\ContentTypeIdentifier');
