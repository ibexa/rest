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
    /**
     * A relation.
     *
     * @var \Ibexa\Contracts\Core\Repository\Values\Content\Relation
     */
    public $relation;

    /**
     * Content ID to which this relation belongs to.
     *
     * @var mixed
     */
    public $contentId;

    /**
     * Version number to which this relation belongs to.
     *
     * @var mixed
     */
    public $versionNo;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Relation $relation
     * @param mixed $contentId
     * @param mixed $versionNo
     */
    public function __construct(Relation $relation, $contentId, $versionNo)
    {
        $this->relation = $relation;
        $this->contentId = $contentId;
        $this->versionNo = $versionNo;
    }
}
