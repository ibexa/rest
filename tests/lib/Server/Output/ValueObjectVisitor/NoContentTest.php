<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Rest\Server\Output\ValueObjectVisitor\NoContent;
use Ibexa\Rest\Server\Values;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class NoContentTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the NoContent visitor.
     *
     * @return string
     */
    public function testVisit(): void
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $noContent = new Values\NoContent();

        $this->getVisitorMock()->expects(self::once())
            ->method('setStatus')
            ->with(self::equalTo(204));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $noContent
        );

        self::assertTrue($generator->isEmpty());
    }

    /**
     * Get the NoContent visitor.
     *
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\NoContent
     */
    protected function internalGetVisitor(): NoContent
    {
        return new NoContent();
    }
}
