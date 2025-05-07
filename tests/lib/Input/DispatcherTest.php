<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Input;

use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Contracts\Rest\Input\Handler;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\Dispatcher;
use Ibexa\Rest\Message;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Dispatcher test class.
 */
class DispatcherTest extends TestCase
{
    protected function getParsingDispatcherMock(): ParsingDispatcher & MockObject
    {
        return $this->createMock(ParsingDispatcher::class);
    }

    public function testParseMissingContentType(): void
    {
        $this->expectException(Parser::class);

        $message = new Message();

        $parsingDispatcher = $this->getParsingDispatcherMock();
        $dispatcher = new Dispatcher($parsingDispatcher);

        $dispatcher->parse($message);
    }

    public function testParseInvalidContentType(): void
    {
        $this->expectException(Parser::class);

        $message = new Message(
            [
                'Content-Type' => 'text/html',
            ]
        );

        $parsingDispatcher = $this->getParsingDispatcherMock();
        $dispatcher = new Dispatcher($parsingDispatcher);

        $dispatcher->parse($message);
    }

    public function testParseMissingFormatHandler(): void
    {
        $this->expectException(Parser::class);

        $message = new Message(
            [
                'Content-Type' => 'text/html+unknown',
            ]
        );

        $parsingDispatcher = $this->getParsingDispatcherMock();
        $dispatcher = new Dispatcher($parsingDispatcher);

        $dispatcher->parse($message);
    }

    public function testParse(): void
    {
        $message = new Message(
            [
                'Content-Type' => 'text/html+format',
            ],
            'Hello world!'
        );

        $parsingDispatcher = $this->getParsingDispatcherMock();
        $parsingDispatcher
            ->expects(self::at(0))
            ->method('parse')
            ->with([42], 'text/html')
            ->willReturn(23);

        $handler = $this->createMock(Handler::class);
        $handler
            ->expects(self::at(0))
            ->method('convert')
            ->with('Hello world!')
            ->willReturn([[42]]);

        $dispatcher = new Dispatcher($parsingDispatcher, ['format' => $handler]);

        self::assertSame(
            23,
            $dispatcher->parse($message)
        );
    }

    /**
     * @todo This is a test for a feature that needs refactoring. There must be
     * a sensible way to submit the called URL to the parser.
     */
    public function testParseSpecialUrlHeader(): void
    {
        $message = new Message(
            [
                'Content-Type' => 'text/html+format',
                'Url' => '/foo/bar',
            ],
            'Hello world!'
        );

        $parsingDispatcher = $this->getParsingDispatcherMock();
        $parsingDispatcher
            ->expects(self::at(0))
            ->method('parse')
            ->with(['someKey' => 'someValue', '__url' => '/foo/bar'], 'text/html')
            ->willReturn(23);

        $handler = $this->createMock(Handler::class);
        $handler
            ->expects(self::at(0))
            ->method('convert')
            ->with('Hello world!')
            ->willReturn(
                [
                        [
                            'someKey' => 'someValue',
                        ],
                    ]
            );

        $dispatcher = new Dispatcher($parsingDispatcher, ['format' => $handler]);

        self::assertSame(
            23,
            $dispatcher->parse($message)
        );
    }

    public function testParseMediaTypeCharset(): void
    {
        $message = new Message(
            [
                'Content-Type' => 'text/html+format; version=1.1; charset=UTF-8',
                'Url' => '/foo/bar',
            ],
            'Hello world!'
        );

        $parsingDispatcher = $this->getParsingDispatcherMock();
        $parsingDispatcher
            ->expects(self::any())
            ->method('parse')
            ->with(self::anything(), 'text/html; version=1.1');

        $handler = $this->createMock(Handler::class);
        $handler
            ->expects(self::any())
            ->method('convert')
            ->willReturn([]);

        $dispatcher = new Dispatcher($parsingDispatcher, ['format' => $handler]);

        $dispatcher->parse($message);
    }
}
