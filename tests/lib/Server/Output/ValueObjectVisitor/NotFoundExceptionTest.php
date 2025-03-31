<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use PHPUnit\Framework\MockObject\MockObject;

class NotFoundExceptionTest extends ExceptionTest
{
    /**
     * Get expected status code.
     *
     * @return int
     */
    protected function getExpectedStatusCode(): int
    {
        return 404;
    }

    /**
     * Get expected message.
     *
     * @return string
     */
    protected function getExpectedMessage(): string
    {
        return 'Not Found';
    }

    /**
     * Get the exception.
     *
     * @return \Exception
     */
    protected function getException(): MockObject
    {
        return $this->getMockForAbstractClass(NotFoundException::class);
    }

    /**
     * Get the exception visitor.
     *
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\NotFoundException
     */
    protected function internalGetVisitor(): ValueObjectVisitor\NotFoundException
    {
        return new ValueObjectVisitor\NotFoundException();
    }
}
