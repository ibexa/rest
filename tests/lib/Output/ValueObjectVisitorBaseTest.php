<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Output;

use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;
use Ibexa\Contracts\Rest\UriParser\UriParserInterface;
use Ibexa\Rest\Output\Generator;
use Ibexa\Rest\Output\Generator\Xml;
use Ibexa\Tests\Rest\AssertXmlTagTrait;
use Ibexa\Tests\Rest\Server;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

abstract class ValueObjectVisitorBaseTest extends Server\BaseTest
{
    use AssertXmlTagTrait;

    protected (Visitor & MockObject)|null $visitorMock = null;

    protected (Response & MockObject)|null $responseMock = null;

    protected Xml|null $generator;

    private (RouterInterface & MockObject)|null $routerMock = null;

    private (RouterInterface & MockObject)|null $templatedRouterMock = null;

    private int $routerCallIndex = 0;

    private int $templatedRouterCallIndex = 0;

    private UriParserInterface&MockObject $uriParser;

    protected function getVisitorMock(): Visitor & MockObject
    {
        if (!isset($this->visitorMock)) {
            $this->visitorMock = $this->createMock(Visitor::class);

            $this->visitorMock
                ->expects(self::any())
                ->method('getResponse')
                ->willReturn($this->getResponseMock());
        }

        return $this->visitorMock;
    }

    protected function getResponseMock(): Response & MockObject
    {
        if (!isset($this->responseMock)) {
            $this->responseMock = $this->getMockBuilder(Response::class)
                ->getMock();
        }

        return $this->responseMock;
    }

    protected function getGenerator(): Xml
    {
        if (!isset($this->generator)) {
            $this->generator = new Generator\Xml(
                new Generator\Xml\FieldTypeHashGenerator(
                    $this->createMock(NormalizerInterface::class),
                ),
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
    protected function assertXPath(\DOMNode $domNode, $xpathExpression)
    {
        $ownerDocument = ($domNode instanceof \DOMDOcument
            ? $domNode
            : $domNode->ownerDocument);

        $xpath = new \DOMXPath($ownerDocument);

        self::assertTrue(
            $xpath->evaluate("boolean({$xpathExpression})", $domNode),
            "XPath expression '{$xpathExpression}' resulted in an empty node set."
        );
    }

    protected function getVisitor()
    {
        $visitor = $this->internalGetVisitor();
        $visitor->setUriParser($this->getUriParser());
        $visitor->setRouter($this->getRouterMock());
        $visitor->setTemplateRouter($this->getTemplatedRouterMock());

        return $visitor;
    }

    protected function getUriParser(): UriParserInterface & MockObject
    {
        if (!isset($this->uriParser)) {
            $this->uriParser = $this->createMock(UriParserInterface::class);
        }

        return $this->uriParser;
    }

    protected function getRouterMock(): RouterInterface & MockObject
    {
        if (!isset($this->routerMock)) {
            $this->routerMock = $this->createMock(RouterInterface::class);
        }

        return $this->routerMock;
    }

    /**
     * Resets the router mock and its expected calls index & list.
     */
    protected function resetRouterMock()
    {
        $this->routerMock = null;
    }

    /**
     * Adds an expectation to the routerMock. Expectations must be added sequentially.
     *
     * @param string $routeName
     * @param array $arguments
     * @param string $returnValue
     */
    protected function addRouteExpectation($routeName, $arguments, $returnValue)
    {
        $this->getRouterMock()
            ->expects(self::at($this->routerCallIndex++))
            ->method('generate')
            ->with(
                self::equalTo($routeName),
                self::equalTo($arguments)
            )
            ->willReturn($returnValue);
    }

    /**
     * @return \Symfony\Component\Routing\RouterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getTemplatedRouterMock()
    {
        if (!isset($this->templatedRouterMock)) {
            $this->templatedRouterMock = $this->createMock(RouterInterface::class);
        }

        return $this->templatedRouterMock;
    }

    /**
     * Adds an expectation to the templatedRouterMock. Expectations must be added sequentially.
     *
     * @param array<string, string> $arguments
     */
    protected function addTemplatedRouteExpectation(string $routeName, array $arguments, string $returnValue): void
    {
        $this->getTemplatedRouterMock()
            ->expects(self::at($this->templatedRouterCallIndex++))
            ->method('generate')
            ->with(
                self::equalTo($routeName),
                self::equalTo($arguments)
            )
            ->willReturn($returnValue);
    }

    abstract protected function internalGetVisitor(): ValueObjectVisitor;
}
