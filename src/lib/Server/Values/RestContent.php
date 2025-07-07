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
 * REST Content, as received by /content/objects/<ID>.
 *
 * Might have a "Version" (aka Content in the Public API) embedded
 */
class RestContent extends RestValue
{
    public ContentInfo $contentInfo;

    public Location $mainLocation;

    public Content $currentVersion;

    public ContentType $contentType;

    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\Content\Relation[]
     */
    public array $relations;

    /**
     * Path that was used to load this content.
     */
    public string $path;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType|null $contentType Can only be null if $currentVersion is
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Relation[]|null $relations Can only be null if $currentVersion is
     */
    public function __construct(
        ContentInfo $contentInfo,
        Location $mainLocation = null,
        Content $currentVersion = null,
        ContentType $contentType = null,
        array $relations = null,
        ?string $path = null
    ) {
        $this->contentInfo = $contentInfo;
        $this->mainLocation = $mainLocation;
        $this->currentVersion = $currentVersion;
        $this->contentType = $contentType;
        $this->relations = $relations;
        $this->path = $path;
    }
}
