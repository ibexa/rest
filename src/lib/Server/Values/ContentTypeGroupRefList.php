<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup;
use Ibexa\Rest\Value as RestValue;

/**
 * ContentTypeGroup list view model.
 */
class ContentTypeGroupRefList extends RestValue
{
    public ContentType $contentType;

    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup[]
     */
    public array $contentTypeGroups;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup[] $contentTypeGroups
     */
    public function __construct(ContentType $contentType, array $contentTypeGroups)
    {
        $this->contentType = $contentType;
        $this->contentTypeGroups = $contentTypeGroups;
    }
}
