<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Rest\Server\Output\ValueObjectVisitor\Options;
use Ibexa\Rest\Server\Values;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTestCase;

class OptionsTest extends ValueObjectVisitorBaseTestCase
{
    /**
     * Test the NoContent visitor.
     */
    public function testVisit(): void
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $noContent = new Values\Options(['GET', 'POST']);

        $this->getVisitorMock()->expects($this->once())
            ->method('setStatus')
            ->with(self::equalTo(200));

        $headers = [];
        $this->getVisitorMock()->expects($this->exactly(2))
            ->method('setHeader')
            ->willReturnCallback(static function (
                string $name,
                mixed $value
            ) use (&$headers): void {
                $headers[] = [$name, $value];
            });

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $noContent
        );

        self::assertSame([['Allow', 'GET,POST'], ['Content-Length', '0']], $headers);
    }

    protected function internalGetVisitor(): Options
    {
        return new Options();
    }
}
