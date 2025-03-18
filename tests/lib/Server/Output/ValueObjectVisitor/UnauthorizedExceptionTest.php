<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;

class UnauthorizedExceptionTest extends ExceptionTest
{
    /**
     * Get expected status code.
     *
     * @return int
     */
    protected function getExpectedStatusCode(): int
    {
        return 401;
    }

    /**
     * Get expected message.
     *
     * @return string
     */
    protected function getExpectedMessage(): string
    {
        return 'Unauthorized';
    }

    /**
     * Gets the exception.
     *
     * @return \Exception
     */
    protected function getException(): \Exception
    {
        return $this->getMockForAbstractClass(UnauthorizedException::class);
    }

    /**
     * Gets the exception visitor.
     *
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\UnauthorizedException
     */
    protected function internalGetVisitor(): ValueObjectVisitor\Exception
    {
        return new ValueObjectVisitor\UnauthorizedException();
    }
}
