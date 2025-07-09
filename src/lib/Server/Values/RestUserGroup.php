<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Rest\Value as RestValue;

/**
 * REST UserGroup, as received by /user/groups/<path>.
 */
class RestUserGroup extends RestValue
{
    public Content $content;

    public ContentType $contentType;

    public ContentInfo $contentInfo;

    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\Content\Relation[]
     */
    public array $relations;

    public Location $mainLocation;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Relation[] $relations
     */
    public function __construct(
        Content $content,
        ContentType $contentType,
        ContentInfo $contentInfo,
        Location $mainLocation,
        array $relations
    ) {
        $this->content = $content;
        $this->contentType = $contentType;
        $this->contentInfo = $contentInfo;
        $this->mainLocation = $mainLocation;
        $this->relations = $relations;
    }
}
