<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Output\Generator\Json;

use AllowDynamicProperties;
use Ibexa\Rest\Output\Generator\Data\DataObjectInterface;

/**
 * Json object.
 *
 * Special JSON object (\stdClass) implementation, which allows to access the
 * parent object it is assigned to again.
 */
#[AllowDynamicProperties]
final class JsonObject implements DataObjectInterface
{
    /**
     * Reference to the parent node.
     */
    private ?DataObjectInterface $_ref_parent;

    /**
     * Construct from optional parent node.
     */
    public function __construct(?DataObjectInterface $_ref_parent = null)
    {
        $this->_ref_parent = $_ref_parent;
    }

    /**
     * Get parent of the current node.
     */
    public function getParent(): ?DataObjectInterface
    {
        return $this->_ref_parent;
    }
}
