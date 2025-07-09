<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

/**
 * ForbiddenException value object visitor.
 */
class ForbiddenException extends Exception
{
    /**
     * Returns HTTP status code.
     */
    protected function getStatus(): int
    {
        return 403;
    }
}
