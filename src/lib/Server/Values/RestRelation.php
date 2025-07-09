<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Contracts\Core\Repository\Values\Content\Relation;
use Ibexa\Rest\Value as RestValue;

/**
 * RestRelation view model.
 */
class RestRelation extends RestValue
{
    public Relation $relation;

    /**
     * Content ID to which this relation belongs to.
     */
    public int $contentId;

    /**
     * Version number to which this relation belongs to.
     */
    public int $versionNo;

    public function __construct(Relation $relation, int $contentId, int $versionNo)
    {
        $this->relation = $relation;
        $this->contentId = $contentId;
        $this->versionNo = $versionNo;
    }
}
