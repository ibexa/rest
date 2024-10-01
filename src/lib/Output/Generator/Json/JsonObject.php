<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Output\Generator\Json;

use AllowDynamicProperties;

/**
 * Json object.
 *
 * Special JSON object (\stdClass) implementation, which allows to access the
 * parent object it is assigned to again.
 */
#[AllowDynamicProperties]
class JsonObject
{
    /**
     * Reference to the parent node.
     *
     * @var \Ibexa\Rest\Output\Generator\Json\JsonObject
     */
    protected $_ref_parent;

    /**
     * Construct from optional parent node.
     *
     * @param mixed $_ref_parent
     */
    public function __construct($_ref_parent = null)
    {
        $this->_ref_parent = $_ref_parent;
    }

    /**
     * Get Parent of current node.
     *
     * @return \Ibexa\Rest\Output\Generator\Json\JsonObject
     */
    public function getParent()
    {
        return $this->_ref_parent;
    }
}
