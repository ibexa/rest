<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Output;

use DOMNode;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;
use Ibexa\Contracts\Rest\UriParser\UriParserInterface;
use Ibexa\Rest\Output\Generator\Xml;
use Ibexa\Tests\Rest\AssertXmlTagTrait;
use Ibexa\Tests\Rest\Server;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

abstract class ValueObjectVisitorBaseTestCase extends Server\BaseTestCase
{
    use AssertXmlTagTrait;

    protected (Visitor & MockObject) | null $visitorMock = null;

    protected Response | null $responseMock = null;

    protected Xml | null $generator;

    private (RouterInterface & Stub) | null $routerMock = null;

    private (RouterInterface & Stub) | null $templatedRouterMock = null;

    /** @var array<int, array{string, array<string, mixed>, string}> */
    private array $routeExpectations = [];

    /** @var array<int, array{string, array<string, mixed>, string}> */
    private array $templatedRouteExpectations = [];

    private bool $routerMockConfigured = false;

    private bool $templatedRouterMockConfigured = false;

    private UriParserInterface & Stub $uriParser;

    private int $routeCallIndex = 0;

    private int $templatedRouteCallIndex = 0;

    protected function getVisitorMock(): Visitor & MockObject
    {
        if (!isset($this->visitorMock)) {
            $this->visitorMock = $this->createMock(Visitor::class);
            $this->visitorMock
                ->method('getResponse')
                ->willReturn($this->getResponseMock());
        }

        return $this->visitorMock;
    }

    protected function getResponseMock(): Response
    {
        if (!isset($this->responseMock)) {
            $this->responseMock = new Response();
        }

        return $this->responseMock;
    }

    protected function getGenerator(): Xml
    {
        if (!isset($this->generator)) {
            $this->generator = new Xml(
                new Xml\FieldTypeHashGenerator(
                    self::createStub(NormalizerInterface::class),
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
     */
    protected function assertXPath(
        DOMNode $domNode,
        string $xpathExpression
    ): void {
        $ownerDocument = ($domNode instanceof \DOMDOcument
            ? $domNode
            : $domNode->ownerDocument);

        $xpath = new \DOMXPath($ownerDocument);

        self::assertTrue(
            $xpath->evaluate("boolean({$xpathExpression})", $domNode),
            "XPath expression '{$xpathExpression}' resulted in an empty node set."
        );
    }

    protected function getVisitor(): ValueObjectVisitor
    {
        $visitor = $this->internalGetVisitor();
        $visitor->setUriParser($this->getUriParser());
        $visitor->setRouter($this->getRouterMock());
        $visitor->setTemplateRouter($this->getTemplatedRouterMock());

        return $visitor;
    }

    protected function getUriParser(): UriParserInterface & Stub
    {
        if (!isset($this->uriParser)) {
            $this->uriParser = self::createStub(UriParserInterface::class);
        }

        return $this->uriParser;
    }

    protected function getRouterMock(): RouterInterface & Stub
    {
        if (!isset($this->routerMock)) {
            $this->routerMock = self::createStub(RouterInterface::class);
        }

        return $this->routerMock;
    }

    /**
     * Resets the router mock and its expected calls index & list.
     */
    protected function resetRouterMock(): void
    {
        $this->routerMock = null;
        $this->routeExpectations = [];
        $this->routeCallIndex = 0;
        $this->routerMockConfigured = false;
    }

    /**
     * Adds an expectation to the routerMock. Expectations must be added sequentially.
     */
    protected function addRouteExpectation(
        string $routeName,
        array $arguments,
        string $returnValue
    ): void {
        $this->routeExpectations[] = [$routeName, $arguments, $returnValue];

        if ($this->routerMockConfigured) {
            return;
        }
        $this->routerMockConfigured = true;

        $this->getRouterMock()
            ->method('generate')
            ->willReturnCallback(function (
                string $routeName,
                array $arguments = []
            ): string {
                $callIndex = $this->routeCallIndex++;

                // Calls beyond the registered expectations are not asserted, mirroring the
                // permissive behaviour of the removed self::at() matcher.
                if (!array_key_exists($callIndex, $this->routeExpectations)) {
                    return '';
                }
                [$expectedRouteName, $expectedArguments, $returnValue] = $this->routeExpectations[$callIndex];

                self::assertSame($expectedRouteName, $routeName);
                self::assertEquals($expectedArguments, $arguments);

                return $returnValue;
            });
    }

    protected function getTemplatedRouterMock(): RouterInterface & Stub
    {
        if (!isset($this->templatedRouterMock)) {
            $this->templatedRouterMock = self::createStub(RouterInterface::class);
        }

        return $this->templatedRouterMock;
    }

    /**
     * Adds an expectation to the templatedRouterMock. Expectations must be added sequentially.
     *
     * @param array<string, string> $arguments
     */
    protected function addTemplatedRouteExpectation(
        string $routeName,
        array $arguments,
        string $returnValue
    ): void {
        $this->templatedRouteExpectations[] = [$routeName, $arguments, $returnValue];

        if ($this->templatedRouterMockConfigured) {
            return;
        }
        $this->templatedRouterMockConfigured = true;

        $this->getTemplatedRouterMock()
            ->method('generate')
            ->willReturnCallback(function (
                string $routeName,
                array $arguments = []
            ): string {
                $callIndex = $this->templatedRouteCallIndex++;

                // Calls beyond the registered expectations are not asserted, mirroring the
                // permissive behaviour of the removed self::at() matcher.
                if (!array_key_exists($callIndex, $this->templatedRouteExpectations)) {
                    return '';
                }
                [$expectedRouteName, $expectedArguments, $returnValue] = $this->templatedRouteExpectations[$callIndex];

                self::assertSame($expectedRouteName, $routeName);
                self::assertEquals($expectedArguments, $arguments);

                return $returnValue;
            });
    }

    protected function assertPostConditions(): void
    {
        parent::assertPostConditions();

        self::assertGreaterThanOrEqual(
            count($this->routeExpectations),
            $this->routeCallIndex,
            'Not all expected router calls were made.'
        );
        self::assertGreaterThanOrEqual(
            count($this->templatedRouteExpectations),
            $this->templatedRouteCallIndex,
            'Not all expected templated router calls were made.'
        );
    }

    abstract protected function internalGetVisitor(): ValueObjectVisitor;
}
