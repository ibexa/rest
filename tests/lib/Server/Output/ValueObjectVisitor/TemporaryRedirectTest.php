<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Rest\Server\Output\ValueObjectVisitor\TemporaryRedirect;
use Ibexa\Rest\Server\Values;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class TemporaryRedirectTest extends ValueObjectVisitorBaseTest
{
    public function testVisit(): void
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $redirect = new Values\TemporaryRedirect('/some/redirect/uri');

        $this->getVisitorMock()->expects(self::once())
            ->method('setStatus')
            ->with(self::equalTo(307));
        $this->getVisitorMock()->expects(self::once())
            ->method('setHeader')
            ->with(self::equalTo('Location'), self::equalTo('/some/redirect/uri'));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $redirect
        );

        self::assertTrue($generator->isEmpty());
    }

    protected function internalGetVisitor(): TemporaryRedirect
    {
        return new TemporaryRedirect();
    }
}
