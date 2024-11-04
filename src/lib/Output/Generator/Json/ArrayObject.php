<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Output\Generator\Json;

use AllowDynamicProperties;
use ArrayObject as NativeArrayObject;
use Ibexa\Rest\Output\Generator\Data\DataObjectInterface;

/**
 * Json array object.
 *
 * Special JSON array object implementation, which allows to access the
 * parent object it is assigned to again.
 */
#[AllowDynamicProperties]
class ArrayObject extends NativeArrayObject implements DataObjectInterface
{
    /**
     * Reference to the parent node.
     */
    protected ?DataObjectInterface $_ref_parent;

    /**
     * Construct from optional parent node.
     */
    public function __construct(?DataObjectInterface $_ref_parent)
    {
        $this->_ref_parent = $_ref_parent;

        parent::__construct();
    }

    /**
     * Get Parent of current node.
     */
    public function getParent(): ?DataObjectInterface
    {
        return $this->_ref_parent;
    }
}
