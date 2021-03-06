<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

/**
 * UnauthorizedException value object visitor.
 */
class UnauthorizedException extends Exception
{
    /**
     * Returns HTTP status code.
     *
     * @return int
     */
    protected function getStatus()
    {
        return 401;
    }
}

class_alias(UnauthorizedException::class, 'EzSystems\EzPlatformRest\Server\Output\ValueObjectVisitor\UnauthorizedException');
