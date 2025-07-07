<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Rest\Value as RestValue;

/**
 * Relation list view model.
 */
class RelationList extends RestValue
{
    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\Content\Relation[]
     */
    public array $relations;

    /**
     * Content ID to which this relation belongs to.
     */
    public int $contentId;

    /**
     * Version number to which this relation belongs to.
     */
    public int $versionNo;

    /**
     * Path used to load the list of relations.
     */
    public ?string $path;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Relation[] $relations
     */
    public function __construct(array $relations, int $contentId, int $versionNo, ?string $path = null)
    {
        $this->relations = $relations;
        $this->contentId = $contentId;
        $this->versionNo = $versionNo;
        $this->path = $path;
    }
}
