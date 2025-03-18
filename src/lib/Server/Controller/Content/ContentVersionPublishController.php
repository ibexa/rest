<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Content;

use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values\NoContent;

class ContentVersionPublishController extends RestController
{
    /**
     * The content version is published.
     *
     * @param mixed $contentId
     * @param mixed $versionNumber
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException if version $versionNumber isn't a draft
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function publishVersion(int $contentId, ?int $versionNumber): NoContent
    {
        $versionInfo = $this->repository->getContentService()->loadVersionInfo(
            $this->repository->getContentService()->loadContentInfo($contentId),
            $versionNumber
        );

        if (!$versionInfo->isDraft()) {
            throw new ForbiddenException('Only versions with DRAFT status can be published');
        }

        $this->repository->getContentService()->publishVersion(
            $versionInfo
        );

        return new NoContent();
    }
}
