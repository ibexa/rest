<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\ContentType;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values;

class ContentTypeCopyController extends RestController
{
    protected ContentTypeService $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Copies a content type. The identifier of the copy is changed to
     * copy_of_<originalBaseIdentifier>_<newTypeId> and a new remoteId is generated.
     *
     * @param $contentTypeId
     *
     * @return \Ibexa\Rest\Server\Values\ResourceCreated
     */
    public function copyContentType($contentTypeId)
    {
        $copiedContentType = $this->contentTypeService->copyContentType(
            $this->contentTypeService->loadContentType($contentTypeId)
        );

        return new Values\ResourceCreated(
            $this->router->generate(
                'ibexa.rest.load_content_type',
                ['contentTypeId' => $copiedContentType->id]
            )
        );
    }
}
