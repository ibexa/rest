<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

/**
 * InvalidArgumentException value object visitor.
 */
class InvalidArgumentException extends Exception
{
    /**
     * Returns HTTP status code.
     */
    protected function getStatus(): int
    {
        return 406;
    }
}
