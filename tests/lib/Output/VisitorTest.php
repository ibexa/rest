<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Output;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitorDispatcher;
use Ibexa\Contracts\Rest\Output\Visitor;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\HttpFoundation\Response;

/**
 * Visitor test.
 */
class VisitorTest extends TestCase
{
    public function testVisitDocument()
    {
        $data = new stdClass();

        $generator = $this->getGeneratorMock();
        $generator
            ->expects(self::at(1))
            ->method('startDocument')
            ->with($data);

        $generator
            ->expects(self::at(2))
            ->method('isEmpty')
            ->willReturn(false);

        $generator
            ->expects(self::at(3))
            ->method('endDocument')
            ->with($data)
            ->willReturn('Hello world!');

        $visitor = $this->getMockBuilder(Visitor::class)
            ->setMethods(['visitValueObject'])
            ->setConstructorArgs([$generator, $this->getValueObjectDispatcherMock()])
            ->getMock();

        self::assertEquals(
            new Response('Hello world!', 200, []),
            $visitor->visit($data)
        );
    }

    public function testVisitEmptyDocument()
    {
        $data = new stdClass();

        $generator = $this->getGeneratorMock();
        $generator
            ->expects(self::at(1))
            ->method('startDocument')
            ->with($data);

        $generator
            ->expects(self::at(2))
            ->method('isEmpty')
            ->willReturn(true);

        $generator
            ->expects(self::never())
            ->method('endDocument');

        $visitor = $this->getMockBuilder(Visitor::class)
            ->setMethods(['visitValueObject'])
            ->setConstructorArgs([$generator, $this->getValueObjectDispatcherMock()])
            ->getMock();

        self::assertEquals(
            new Response(null, 200, []),
            $visitor->visit($data)
        );
    }

    public function testVisitValueObject()
    {
        $data = new stdClass();

        /** @var \PHPUnit\Framework\MockObject\MockObject|\Ibexa\Contracts\Rest\Output\Generator $generatorMock */
        $generatorMock = $this->getGeneratorMock();

        $valueObjectDispatcherMock = $this->getValueObjectDispatcherMock();
        $valueObjectDispatcherMock
            ->expects(self::once())
            ->method('visit')
            ->with($data);

        $visitor = new Visitor($generatorMock, $valueObjectDispatcherMock);
        $visitor->visit($data);
    }

    public function testSetHeaders()
    {
        $data = new stdClass();

        $visitor = $this->getVisitorMock();

        $visitor->setHeader('Content-Type', 'text/xml');
        self::assertEquals(
            new Response(
                null,
                200,
                [
                    'Content-Type' => 'text/xml',
                ]
            ),
            $visitor->visit($data)
        );
    }

    /**
     * @todo This is a test for a feature that needs refactoring.
     *
     * @see \Ibexa\Contracts\Rest\Output\Visitor::visit
     */
    public function testSetFilteredHeaders()
    {
        $data = new stdClass();

        $visitor = $this->getVisitorMock();

        $visitor->setHeader('Content-Type', 'text/xml');
        $visitor->setHeader('Accept-Patch', false);
        self::assertEquals(
            new Response(
                null,
                200,
                [
                    'Content-Type' => 'text/xml',
                ]
            ),
            $visitor->visit($data)
        );
    }

    public function testSetHeadersNoOverwrite()
    {
        $data = new stdClass();

        $visitor = $this->getVisitorMock();

        $visitor->setHeader('Content-Type', 'text/xml');
        $visitor->setHeader('Content-Type', 'text/html');
        self::assertEquals(
            new Response(
                null,
                200,
                [
                    'Content-Type' => 'text/xml',
                ]
            ),
            $visitor->visit($data)
        );
    }

    public function testSetHeaderResetAfterVisit()
    {
        $data = new stdClass();

        $visitor = $this->getVisitorMock();

        $visitor->setHeader('Content-Type', 'text/xml');

        $visitor->visit($data);
        $result = $visitor->visit($data);

        self::assertEquals(
            new Response(
                null,
                200,
                []
            ),
            $result
        );
    }

    public function testSetStatusCode()
    {
        $data = new stdClass();

        $visitor = $this->getVisitorMock();

        $visitor->setStatus(201);
        self::assertEquals(
            new Response(
                null,
                201
            ),
            $visitor->visit($data)
        );
    }

    public function testSetStatusCodeNoOverride()
    {
        $data = new stdClass();

        $visitor = $this->getVisitorMock();

        $visitor->setStatus(201);
        $visitor->setStatus(404);

        self::assertEquals(
            new Response(
                null,
                201
            ),
            $visitor->visit($data)
        );
    }

    /**
     * @return \Ibexa\Contracts\Rest\Output\ValueObjectVisitorDispatcher|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getValueObjectDispatcherMock()
    {
        return $this->createMock(ValueObjectVisitorDispatcher::class);
    }

    protected function getGeneratorMock()
    {
        return $this->createMock(Generator::class);
    }

    protected function getVisitorMock()
    {
        return $this->getMockBuilder(Visitor::class)
            ->setMethods(['visitValueObject'])
            ->setConstructorArgs(
                [
                    $this->getGeneratorMock(),
                    $this->getValueObjectDispatcherMock(),
                ]
            )
            ->getMock();
    }
}

class_alias(VisitorTest::class, 'EzSystems\EzPlatformRest\Tests\Output\VisitorTest');
