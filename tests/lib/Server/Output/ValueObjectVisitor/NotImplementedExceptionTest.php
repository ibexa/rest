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
    /**
     * Get expected status code.
     *
     * @return int
     */
    protected function getExpectedStatusCode(): int
    {
        return 501;
    }

    /**
     * Get expected message.
     *
     * @return string
     */
    protected function getExpectedMessage(): string
    {
        return 'Not Implemented';
    }

    /**
     * Gets the exception.
     *
     * @return \Exception
     */
    protected function getException(): NotImplementedException
    {
        return new NotImplementedException('Test');
    }

    /**
     * Gets the exception visitor.
     *
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\NotImplementedException
     */
    protected function internalGetVisitor(): ValueObjectVisitor\NotImplementedException
    {
        return new ValueObjectVisitor\NotImplementedException();
    }
}
