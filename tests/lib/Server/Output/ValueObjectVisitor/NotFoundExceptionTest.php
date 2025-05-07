<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Exception;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use PHPUnit\Framework\MockObject\MockObject;

class NotFoundExceptionTest extends ExceptionTest
{
    protected function getExpectedStatusCode(): int
    {
        return 404;
    }

    protected function getExpectedMessage(): string
    {
        return 'Not Found';
    }

    protected function getException(): Exception & MockObject
    {
        return $this->getMockForAbstractClass(NotFoundException::class);
    }

    protected function internalGetVisitor(): ValueObjectVisitor\NotFoundException
    {
        return new ValueObjectVisitor\NotFoundException();
    }
}
