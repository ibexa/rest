<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Rest\Server\Values;

use Ibexa\Rest\Value as RestValue;

/**
 * ContentTypeGroup list view model.
 */
class ContentTypeGroupList extends RestValue
{
    /**
     * Content type groups.
     *
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup[]
     */
    public $contentTypeGroups;

    /**
     * Construct.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup[] $contentTypeGroups
     */
    public function __construct(array $contentTypeGroups)
    {
        $this->contentTypeGroups = $contentTypeGroups;
    }
}

class_alias(ContentTypeGroupList::class, 'EzSystems\EzPlatformRest\Server\Values\ContentTypeGroupList');
