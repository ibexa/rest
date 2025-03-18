<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Rest\Server\Output\ValueObjectVisitor\ResourceCreated;
use Ibexa\Rest\Server\Values;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class ResourceCreatedTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the ResourceCreated visitor.
     *
     * @return string
     */
    public function testVisit(): void
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $resourceCreated = new Values\ResourceCreated(
            '/some/redirect/uri'
        );

        $this->getVisitorMock()->expects(self::once())
            ->method('setStatus')
            ->with(self::equalTo(201));
        $this->getVisitorMock()->expects(self::once())
            ->method('setHeader')
            ->with(self::equalTo('Location'), self::equalTo('/some/redirect/uri'));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $resourceCreated
        );

        self::assertTrue($generator->isEmpty());
    }

    /**
     * Get the ResourceCreated visitor.
     *
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\ResourceCreated
     */
    protected function internalGetVisitor(): ResourceCreated
    {
        return new ResourceCreated();
    }
}
