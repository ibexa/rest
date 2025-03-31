<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;

class ForbiddenExceptionTest extends ExceptionTest
{
    /**
     * Get expected status code.
     *
     * @return int
     */
    protected function getExpectedStatusCode(): int
    {
        return 403;
    }

    /**
     * Get expected message.
     *
     * @return string
     */
    protected function getExpectedMessage(): string
    {
        return 'Forbidden';
    }

    /**
     * Gets the exception.
     *
     * @return \Exception
     */
    protected function getException(): ForbiddenException
    {
        return new ForbiddenException('Test');
    }

    /**
     * Gets the exception visitor.
     *
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\ForbiddenException
     */
    protected function internalGetVisitor(): ValueObjectVisitor\ForbiddenException
    {
        return new ValueObjectVisitor\ForbiddenException();
    }
}
