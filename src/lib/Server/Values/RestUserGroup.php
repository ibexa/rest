<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\Relation;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Rest\Value as RestValue;

/**
 * REST UserGroup, as received by /user/groups/<path>.
 */
class RestUserGroup extends RestValue
{
    /**
     * @var Content
     */
    public $content;

    /**
     * @var ContentType
     */
    public $contentType;

    /**
     * @var ContentInfo
     */
    public $contentInfo;

    /**
     * @var Relation[]
     */
    public $relations;

    /**
     * @var Location
     */
    public $mainLocation;

    /**
     * Construct.
     *
     * @param Content $content
     * @param ContentType $contentType
     * @param ContentInfo $contentInfo
     * @param Location $mainLocation
     * @param Relation[] $relations
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

class_alias(RestUserGroup::class, 'EzSystems\EzPlatformRest\Server\Values\RestUserGroup');
