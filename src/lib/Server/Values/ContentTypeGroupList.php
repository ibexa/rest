<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup;
use Ibexa\Rest\Value as RestValue;

/**
 * ContentTypeGroup list view model.
 */
class ContentTypeGroupList extends RestValue
{
    /**
     * Content type groups.
     *
     * @var ContentTypeGroup[]
     */
    public $contentTypeGroups;

    /**
     * Construct.
     *
     * @param ContentTypeGroup[] $contentTypeGroups
     */
    public function __construct(array $contentTypeGroups)
    {
        $this->contentTypeGroups = $contentTypeGroups;
    }
}

class_alias(ContentTypeGroupList::class, 'EzSystems\EzPlatformRest\Server\Values\ContentTypeGroupList');
