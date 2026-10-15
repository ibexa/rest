<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Output;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitorDispatcher;
use Ibexa\Contracts\Rest\Output\Visitor;
use PHPUnit\Framework\MockObject\MockObject;
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
        $calls = [];

        $generator = $this->getGeneratorMock();
        $this->expectRecordedCall($generator, 'reset', $calls);
        $this->expectRecordedCall($generator, 'startDocument', $calls, [$data]);
        $this->expectRecordedCall($generator, 'isEmpty', $calls, [], false);
        $this->expectRecordedCall($generator, 'endDocument', $calls, [$data], 'Hello world!');

        $visitor = $this->getMockBuilder(Visitor::class)
            ->setMethods(['visitValueObject'])
            ->setConstructorArgs([$generator, $this->createStub(ValueObjectVisitorDispatcher::class)])
            ->getMock();
        $visitor->expects($this->once())->method('visitValueObject')->with($data);

        $this->assertEquals(
            new Response('Hello world!', 200, []),
            $visitor->visit($data)
        );
        $this->assertSame(['reset', 'startDocument', 'isEmpty', 'endDocument'], $calls);
    }

    public function testVisitEmptyDocument()
    {
        $data = new stdClass();
        $calls = [];

        $generator = $this->getGeneratorMock();
        $this->expectRecordedCall($generator, 'reset', $calls);
        $this->expectRecordedCall($generator, 'startDocument', $calls, [$data]);
        $this->expectRecordedCall($generator, 'isEmpty', $calls, [], true);

        $generator
            ->expects($this->never())
            ->method('endDocument');

        $visitor = $this->getMockBuilder(Visitor::class)
            ->setMethods(['visitValueObject'])
            ->setConstructorArgs([$generator, $this->createStub(ValueObjectVisitorDispatcher::class)])
            ->getMock();
        $visitor->expects($this->once())->method('visitValueObject')->with($data);

        $this->assertEquals(
            new Response(null, 200, []),
            $visitor->visit($data)
        );
        $this->assertSame(['reset', 'startDocument', 'isEmpty'], $calls);
    }

    /**
     * Expects exactly one call of $method with $arguments and records its name in $calls,
     * to assert order across methods of one mock.
     *
     * @param list<string> $calls
     * @param list<mixed> $arguments
     * @param mixed $returnValue
     */
    private function expectRecordedCall(
        MockObject $mock,
        string $method,
        array &$calls,
        array $arguments = [],
        $returnValue = null
    ): void {
        $builder = $mock->expects($this->once())->method($method);
        if ($arguments !== []) {
            $builder->with(...$arguments);
        }
        $builder->willReturnCallback(static function () use (&$calls, $method, $returnValue) {
            $calls[] = $method;

            return $returnValue;
        });
    }

    public function testVisitValueObject()
    {
        $data = new stdClass();

        $generatorMock = $this->createStub(Generator::class);

        $valueObjectDispatcherMock = $this->getValueObjectDispatcherMock();
        $valueObjectDispatcherMock
            ->expects($this->once())
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
        $this->assertEquals(
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
     * @see Visitor::visit
     */
    public function testSetFilteredHeaders()
    {
        $data = new stdClass();

        $visitor = $this->getVisitorMock();

        $visitor->setHeader('Content-Type', 'text/xml');
        $visitor->setHeader('Accept-Patch', false);
        $this->assertEquals(
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
        $this->assertEquals(
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

        $this->assertEquals(
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
        $this->assertEquals(
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

        $this->assertEquals(
            new Response(
                null,
                201
            ),
            $visitor->visit($data)
        );
    }

    /**
     * @return ValueObjectVisitorDispatcher|MockObject
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
                    $this->createStub(Generator::class),
                    $this->createStub(ValueObjectVisitorDispatcher::class),
                ]
            )
            ->getMock();
    }
}

class_alias(VisitorTest::class, 'EzSystems\EzPlatformRest\Tests\Output\VisitorTest');
