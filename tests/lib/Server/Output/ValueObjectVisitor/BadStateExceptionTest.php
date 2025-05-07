<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Exception;
use Ibexa\Contracts\Core\Repository\Exceptions\BadStateException;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use PHPUnit\Framework\MockObject\MockObject;

class BadStateExceptionTest extends ExceptionTest
{
    protected function getExpectedStatusCode(): int
    {
        return 409;
    }

    protected function getExpectedMessage(): string
    {
        return 'Conflict';
    }

    protected function getException(): Exception & MockObject
    {
        return $this->getMockForAbstractClass(BadStateException::class);
    }

    protected function internalGetVisitor(): ValueObjectVisitor\BadStateException
    {
        return new ValueObjectVisitor\BadStateException();
    }
}
