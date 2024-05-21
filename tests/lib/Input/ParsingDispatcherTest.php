<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Input;

use Ibexa\Contracts\Rest\Input\Parser;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * ParsingDispatcher test class.
 */
class ParsingDispatcherTest extends TestCase
{
    public function testParseMissingContentType()
    {
        $this->expectException(\Ibexa\Contracts\Rest\Exceptions\Parser::class);

        $dispatcher = new ParsingDispatcher($this->createMock(EventDispatcherInterface::class));

        $dispatcher->parse([], 'text/unknown');
    }

    public function testParse()
    {
        $parser = $this->createParserMock();
        $dispatcher = new ParsingDispatcher($this->createMock(EventDispatcherInterface::class), ['text/html' => $parser]);

        $parser
            ->expects(self::at(0))
            ->method('parse')
            ->with([42], $dispatcher)
            ->willReturn(23);

        self::assertSame(
            23,
            $dispatcher->parse([42], 'text/html')
        );
    }

    /**
     * Verifies that the charset specified in the Content-Type is ignored.
     */
    public function testParseCharset()
    {
        $parser = $this->createParserMock();
        $dispatcher = new ParsingDispatcher($this->createMock(EventDispatcherInterface::class), ['text/html' => $parser]);

        $parser
            ->expects(self::at(0))
            ->method('parse')
            ->with([42], $dispatcher)
            ->willReturn(23);

        self::assertSame(
            23,
            $dispatcher->parse([42], 'text/html; charset=UTF-8; version=1.0')
        );
    }

    public function testParseVersion()
    {
        $parserVersionOne = $this->createParserMock();
        $parserVersionTwo = $this->createParserMock();
        $dispatcher = new ParsingDispatcher(
            $this->createMock(EventDispatcherInterface::class),
            [
                'text/html' => $parserVersionOne,
                'text/html; version=2' => $parserVersionTwo,
            ]
        );

        $parserVersionOne->expects(self::never())->method('parse');
        $parserVersionTwo->expects(self::once())->method('parse');

        $dispatcher->parse([42], 'text/html; version=2');
    }

    public function testParseStripFormat()
    {
        $parser = $this->createParserMock();
        $dispatcher = new ParsingDispatcher($this->createMock(EventDispatcherInterface::class), ['text/html' => $parser]);

        $parser
            ->expects(self::at(0))
            ->method('parse')
            ->with([42], $dispatcher)
            ->willReturn(23);

        self::assertSame(
            23,
            $dispatcher->parse([42], 'text/html+json')
        );
    }

    /**
     * @return \Ibexa\Contracts\Rest\Input\Parser|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createParserMock()
    {
        return $this->createMock(Parser::class);
    }
}
