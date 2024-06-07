<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

/**
 * UnauthorizedException value object visitor.
 */
final class UnauthorizedException extends Exception
{
    protected function getStatus(): int
    {
        return 401;
    }
}
