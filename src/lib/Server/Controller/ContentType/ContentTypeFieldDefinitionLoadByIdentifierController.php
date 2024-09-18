<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\ContentType;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Request;

class ContentTypeFieldDefinitionLoadByIdentifierController extends RestController
{
    protected ContentTypeService $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function loadContentTypeFieldDefinitionByIdentifier(
        int $contentTypeId,
        string $fieldDefinitionIdentifier,
        Request $request
    ): Values\RestFieldDefinition {
        $contentType = $this->contentTypeService->loadContentType($contentTypeId);
        $fieldDefinition = $contentType->getFieldDefinition($fieldDefinitionIdentifier);
        $path = $this->router->generate(
            'ibexa.rest.load_content_type_field_definition_by_identifier',
            [
                'contentTypeId' => $contentType->id,
                'fieldDefinitionIdentifier' => $fieldDefinitionIdentifier,
            ]
        );

        if ($fieldDefinition === null) {
            throw new Exceptions\NotFoundException(
                sprintf("Field definition not found: '%s'.", $request->getPathInfo())
            );
        }

        return new Values\RestFieldDefinition(
            $contentType,
            $fieldDefinition,
            $path
        );
    }
}
