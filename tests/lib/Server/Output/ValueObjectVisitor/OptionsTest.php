<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Rest\Server\Output\ValueObjectVisitor\Options;
use Ibexa\Rest\Server\Values;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class OptionsTest extends ValueObjectVisitorBaseTest
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

        $noContent = new Values\Options(['GET', 'POST']);

        $this->getVisitorMock()->expects(self::once())
            ->method('setStatus')
            ->with(self::equalTo(200));

        $this->getVisitorMock()->expects(self::exactly(2))
            ->method('setHeader')
            ->willReturnMap(
                ['Allow', 'GET,POST'],
                ['Content-Length', 0]
            );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $noContent
        );
    }

    /**
     * Get the NoContent visitor.
     *
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\NoContent
     */
    protected function internalGetVisitor(): Options
    {
        return new Options();
    }
}
