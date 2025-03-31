<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Rest\Server\Exceptions\BadRequestException;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;

class BadRequestExceptionTest extends ExceptionTest
{
    /**
     * Get expected status code.
     *
     * @return int
     */
    protected function getExpectedStatusCode(): int
    {
        return 400;
    }

    /**
     * Get expected message.
     *
     * @return string
     */
    protected function getExpectedMessage(): string
    {
        return 'Bad Request';
    }

    /**
     * Gets the exception.
     *
     * @return \Exception
     */
    protected function getException(): BadRequestException
    {
        return new BadRequestException('Test');
    }

    /**
     * Gets the exception visitor.
     *
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\BadRequestException
     */
    protected function internalGetVisitor(): ValueObjectVisitor\BadRequestException
    {
        return new ValueObjectVisitor\BadRequestException();
    }
}
