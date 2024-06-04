<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class ConflictTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the Conflict visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $noContent = new Values\Conflict();

        $this->getVisitorMock()->expects(self::once())
            ->method('setStatus')
            ->with(self::equalTo(409));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $noContent
        );

        self::assertTrue($generator->isEmpty());
    }

    /**
     * Get the Conflict visitor.
     *
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\Conflict
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\Conflict();
    }
}
