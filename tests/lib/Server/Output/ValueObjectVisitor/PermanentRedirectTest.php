<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class PermanentRedirectTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the PermanentRedirect visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $redirect = new Values\PermanentRedirect('/some/redirect/uri');

        $this->getVisitorMock()->expects(self::once())
            ->method('setStatus')
            ->with(self::equalTo(301));
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

    /**
     * Get the PermanentRedirect visitor.
     *
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\PermanentRedirect
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\PermanentRedirect();
    }
}

class_alias(PermanentRedirectTest::class, 'EzSystems\EzPlatformRest\Tests\Server\Output\ValueObjectVisitor\PermanentRedirectTest');
