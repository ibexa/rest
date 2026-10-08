<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Output;

use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;
use Ibexa\Rest\Output\Generator;
use Ibexa\Rest\Output\Generator\Xml;
use Ibexa\Rest\RequestParser;
use Ibexa\Tests\Rest\AssertXmlTagTrait;
use Ibexa\Tests\Rest\Server;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

abstract class ValueObjectVisitorBaseTest extends Server\BaseTest
{
    use AssertXmlTagTrait;

    /**
     * Visitor mock.
     *
     * @var Visitor
     */
    protected $visitorMock;

    /**
     * Output generator.
     *
     * @var Xml
     */
    protected $generator;

    /**
     * @var RequestParser
     */
    protected $requestParser;

    /**
     * @var RouterInterface|MockObject
     */
    private $routerMock;

    /**
     * @var RouterInterface|MockObject
     */
    private $templatedRouterMock;

    /** @var array<int, array{string, array<mixed>, string}> */
    private $routeExpectations = [];

    /** @var array<int, array{string, array<mixed>, string}> */
    private $templatedRouteExpectations = [];

    /**
     * Gets the visitor mock.
     *
     * @return Visitor|MockObject
     */
    protected function getVisitorMock()
    {
        if (!isset($this->visitorMock)) {
            $this->visitorMock = $this->createMock(Visitor::class);

            $this->visitorMock
                ->expects($this->any())
                ->method('getResponse')
                ->willReturn($this->getResponseMock());
        }

        return $this->visitorMock;
    }

    /**
     * @return Response|MockObject
     */
    protected function getResponseMock()
    {
        if (!isset($this->responseMock)) {
            $this->responseMock = $this->getMockBuilder(Response::class)
                ->getMock();
        }

        return $this->responseMock;
    }

    /**
     * Gets the output generator.
     *
     * @return Xml
     */
    protected function getGenerator()
    {
        if (!isset($this->generator)) {
            $this->generator = new Xml(
                new Xml\FieldTypeHashGenerator(
                    $this->createStub(NormalizerInterface::class)
                )
            );
        }

        return $this->generator;
    }

    /**
     * Asserts that the given $xpathExpression returns a non empty node set
     * with $domNode as its context.
     *
     * This method asserts that $xpathExpression results in a non-empty node
     * set in context of $domNode, by wrapping the "boolean()" function around
     * it and evaluating it on the document owning $domNode.
     *
     * @param \DOMNode $domNode
     * @param string $xpathExpression
     */
    protected function assertXPath(
        \DOMNode $domNode,
        $xpathExpression
    ) {
        $ownerDocument = ($domNode instanceof \DOMDOcument
            ? $domNode
            : $domNode->ownerDocument);

        $xpath = new \DOMXPath($ownerDocument);

        $this->assertTrue(
            $xpath->evaluate("boolean({$xpathExpression})", $domNode),
            "XPath expression '{$xpathExpression}' resulted in an empty node set."
        );
    }

    protected function getVisitor()
    {
        $visitor = $this->internalGetVisitor();
        $visitor->setRequestParser($this->getRequestParser());
        $visitor->setRouter($this->getRouterMock());
        $visitor->setTemplateRouter($this->getTemplatedRouterMock());

        return $visitor;
    }

    /**
     * @return RequestParser|MockObject
     */
    protected function getRequestParser()
    {
        if (!isset($this->requestParser)) {
            $this->requestParser = $this->createMock(RequestParser::class);
        }

        return $this->requestParser;
    }

    /**
     * @return RouterInterface|MockObject
     */
    protected function getRouterMock()
    {
        if (!isset($this->routerMock)) {
            $this->routerMock = $this->createRouterMock($this->routeExpectations);
        }

        return $this->routerMock;
    }

    /**
     * Resets the router mock and its expected calls index & list.
     */
    protected function resetRouterMock()
    {
        $this->routerMock = null;
        $this->routeExpectations = [];
    }

    protected function assertPostConditions(): void
    {
        parent::assertPostConditions();

        self::assertSame([], $this->routeExpectations, 'Not all expected router calls were made.');
        self::assertSame([], $this->templatedRouteExpectations, 'Not all expected templated router calls were made.');
    }

    /**
     * Router mock which expects calls to generate() in the order given by the (by reference) expectation list.
     *
     * @param array<int, array{string, array<mixed>, string}> $expectations
     *
     * @return RouterInterface&MockObject
     */
    private function createRouterMock(array &$expectations): RouterInterface
    {
        $router = $this->createMock(RouterInterface::class);
        $router
            ->expects($this->any())
            ->method('generate')
            ->willReturnCallback(
                static function (
                    string $name,
                    array $parameters = []
                ) use (&$expectations): string {
                    $expected = array_shift($expectations);
                    if ($expected === null) {
                        // Calls beyond the expected ones are not asserted, same as with a plain mock.
                        return '';
                    }

                    [$expectedName, $expectedParameters, $returnValue] = $expected;
                    self::assertEquals($expectedName, $name);
                    self::assertEquals($expectedParameters, $parameters);

                    return $returnValue;
                }
            );

        return $router;
    }

    /**
     * Adds an expectation to the routerMock. Expectations must be added sequentially.
     *
     * @param string $routeName
     * @param array $arguments
     * @param string $returnValue
     */
    protected function addRouteExpectation(
        $routeName,
        $arguments,
        $returnValue
    ) {
        $this->routeExpectations[] = [$routeName, $arguments, $returnValue];
    }

    /**
     * @return RouterInterface|MockObject
     */
    protected function getTemplatedRouterMock()
    {
        if (!isset($this->templatedRouterMock)) {
            $this->templatedRouterMock = $this->createRouterMock($this->templatedRouteExpectations);
        }

        return $this->templatedRouterMock;
    }

    /**
     * Adds an expectation to the templatedRouterMock. Expectations must be added sequentially.
     *
     * @param string $routeName
     * @param array $arguments
     * @param string $returnValue
     */
    protected function addTemplatedRouteExpectation(
        $routeName,
        $arguments,
        $returnValue
    ) {
        $this->templatedRouteExpectations[] = [$routeName, $arguments, $returnValue];
    }

    /**
     * Must return an instance of the tested visitor object.
     *
     * @return ValueObjectVisitor
     */
    abstract protected function internalGetVisitor();
}

class_alias(ValueObjectVisitorBaseTest::class, 'EzSystems\EzPlatformRest\Tests\Output\ValueObjectVisitorBaseTest');
