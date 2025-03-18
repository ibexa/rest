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
    protected function getExpectedStatusCode(): int
    {
        return 403;
    }

    protected function getExpectedMessage(): string
    {
        return 'Forbidden';
    }

    protected function getException(): ForbiddenException
    {
        return new ForbiddenException('Test');
    }

    protected function internalGetVisitor(): ValueObjectVisitor\ForbiddenException
    {
        return new ValueObjectVisitor\ForbiddenException();
    }
}
