<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\ContentType;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values\RestContentType;

class ContentTypeDraftPublishController extends RestController
{
    protected ContentTypeService $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Publishes a content type draft.
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     */
    public function publishContentTypeDraft(int $contentTypeId): RestContentType
    {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($contentTypeId);

        $this->contentTypeService->publishContentTypeDraft($contentTypeDraft);

        $publishedContentType = $this->contentTypeService->loadContentType($contentTypeDraft->id, Language::ALL);

        return new RestContentType(
            $publishedContentType,
            $publishedContentType->getFieldDefinitions()->toArray()
        );
    }
}
