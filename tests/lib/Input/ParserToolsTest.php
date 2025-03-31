<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Input;

use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\ParserTools;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ParserToolsTest extends TestCase
{
    public function testIsEmbeddedObjectReturnsTrue(): void
    {
        $parserTools = $this->getParserTools();

        self::assertTrue(
            $parserTools->isEmbeddedObject(
                [
                    '_href' => '/foo/bar',
                    '_media-type' => 'application/some-type',
                    'id' => 23,
                ]
            )
        );
    }

    public function testIsEmbeddedObjectReturnsFalse(): void
    {
        $parserTools = $this->getParserTools();

        self::assertFalse(
            $parserTools->isEmbeddedObject(
                [
                    '_href' => '/foo/bar',
                    '_media-type' => 'application/some-type',
                ]
            )
        );
    }

    public function testParseObjectElementEmbedded(): void
    {
        $parserTools = $this->getParserTools();

        $dispatcherMock = $this->createMock(ParsingDispatcher::class);
        $dispatcherMock->expects(self::once())
            ->method('parse')
            ->with(
                self::isType('array'),
                self::equalTo('application/my-type')
            );

        $parsingInput = [
            '_href' => '/foo/bar',
            '_media-type' => 'application/my-type',
            'someContent' => [],
        ];

        self::assertEquals(
            '/foo/bar',
            $parserTools->parseObjectElement($parsingInput, $dispatcherMock)
        );
    }

    public function testParseObjectElementNotEmbedded(): void
    {
        $parserTools = $this->getParserTools();

        $dispatcherMock = $this->createMock(ParsingDispatcher::class);
        $dispatcherMock->expects(self::never())
            ->method('parse');

        $parsingInput = [
            '_href' => '/foo/bar',
            '_media-type' => 'application/my-type',
            '#someTextContent' => 'foo',
        ];

        self::assertEquals(
            '/foo/bar',
            $parserTools->parseObjectElement($parsingInput, $dispatcherMock)
        );
    }

    public function testNormalParseBooleanValue(): void
    {
        $tools = $this->getParserTools();

        self::assertTrue($tools->parseBooleanValue('true'));
        self::assertTrue($tools->parseBooleanValue(true));
        self::assertFalse($tools->parseBooleanValue('false'));
        self::assertFalse($tools->parseBooleanValue(false));
    }

    public function testUnexpectedValueParseBooleanValue(): void
    {
        $this->expectException(RuntimeException::class);

        $this->getParserTools()->parseBooleanValue('whatever but not a boolean');
    }

    protected function getParserTools(): ParserTools
    {
        return new ParserTools();
    }
}
