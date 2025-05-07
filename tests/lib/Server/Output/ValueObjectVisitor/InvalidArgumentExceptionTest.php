<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Exception;
use Ibexa\Contracts\Rest\Exceptions\InvalidArgumentException;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;

class InvalidArgumentExceptionTest extends ExceptionTest
{
    protected function getExpectedStatusCode(): int
    {
        return 406;
    }

    protected function getExpectedMessage(): string
    {
        return 'Not Acceptable';
    }

    protected function getException(): Exception
    {
        return new InvalidArgumentException('Test');
    }

    protected function internalGetVisitor(): ValueObjectVisitor\InvalidArgumentException
    {
        return new ValueObjectVisitor\InvalidArgumentException();
    }
}
