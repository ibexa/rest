<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Rest\Server\Output\ValueObjectVisitor\Conflict;
use Ibexa\Rest\Server\Values;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class ConflictTest extends ValueObjectVisitorBaseTest
{
    public function testVisit(): void
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

    protected function internalGetVisitor(): Conflict
    {
        return new Conflict();
    }
}
