<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Rest\Exceptions\InvalidArgumentException;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;

class InvalidArgumentExceptionTest extends ExceptionTest
{
    /**
     * Get expected status code.
     *
     * @return int
     */
    protected function getExpectedStatusCode(): int
    {
        return 406;
    }

    /**
     * Get expected message.
     *
     * @return string
     */
    protected function getExpectedMessage(): string
    {
        return 'Not Acceptable';
    }

    /**
     * Gets the exception.
     *
     * @return \Exception
     */
    protected function getException(): InvalidArgumentException
    {
        return new InvalidArgumentException('Test');
    }

    /**
     * Gets the exception visitor.
     *
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\InvalidArgumentException
     */
    protected function internalGetVisitor(): ValueObjectVisitor\InvalidArgumentException
    {
        return new ValueObjectVisitor\InvalidArgumentException();
    }
}
