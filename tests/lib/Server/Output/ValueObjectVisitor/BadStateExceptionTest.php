<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Exceptions\BadStateException;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use PHPUnit\Framework\MockObject\MockObject;

class BadStateExceptionTest extends ExceptionTest
{
    /**
     * Get expected status code.
     *
     * @return int
     */
    protected function getExpectedStatusCode(): int
    {
        return 409;
    }

    /**
     * Get expected message.
     *
     * @return string
     */
    protected function getExpectedMessage(): string
    {
        return 'Conflict';
    }

    /**
     * Gets the exception.
     *
     * @return \Exception
     */
    protected function getException(): MockObject
    {
        return $this->getMockForAbstractClass(BadStateException::class);
    }

    /**
     * Gets the exception visitor.
     *
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\BadStateException
     */
    protected function internalGetVisitor(): ValueObjectVisitor\BadStateException
    {
        return new ValueObjectVisitor\BadStateException();
    }
}
