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
    protected function getExpectedStatusCode(): int
    {
        return 400;
    }

    protected function getExpectedMessage(): string
    {
        return 'Bad Request';
    }

    protected function getException(): BadRequestException
    {
        return new BadRequestException('Test');
    }

    protected function internalGetVisitor(): ValueObjectVisitor\BadRequestException
    {
        return new ValueObjectVisitor\BadRequestException();
    }
}
