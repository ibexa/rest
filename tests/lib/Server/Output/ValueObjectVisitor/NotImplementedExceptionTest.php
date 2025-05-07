<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Exceptions\NotImplementedException;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;

class NotImplementedExceptionTest extends ExceptionTest
{
    protected function getExpectedStatusCode(): int
    {
        return 501;
    }

    protected function getExpectedMessage(): string
    {
        return 'Not Implemented';
    }

    protected function getException(): NotImplementedException
    {
        return new NotImplementedException('Test');
    }

    protected function internalGetVisitor(): ValueObjectVisitor\NotImplementedException
    {
        return new ValueObjectVisitor\NotImplementedException();
    }
}
