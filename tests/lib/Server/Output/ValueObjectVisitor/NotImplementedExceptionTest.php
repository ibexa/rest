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
    protected function getExpectedStatusCode()
    {
        return 501;
    }

    /**
     * Get expected message.
     *
     * @return string
     */
    protected function getExpectedMessage()
    {
        return 'Not Implemented';
    }

    /**
     * Gets the exception.
     *
     * @return \Exception
     */
    protected function getException()
    {
        return new NotImplementedException('Test');
    }

    /**
     * Gets the exception visitor.
     *
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\NotImplementedException
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\NotImplementedException();
    }
}

class_alias(NotImplementedExceptionTest::class, 'EzSystems\EzPlatformRest\Tests\Server\Output\ValueObjectVisitor\NotImplementedExceptionTest');
