<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser;

use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Rest\Server\Input\Parser\URLWildcardCreate;

class URLWildcardCreateTest extends BaseTest
{
    public function testParse(): void
    {
        $inputArray = [
            'sourceUrl' => '/source/url',
            'destinationUrl' => '/destination/url',
            'forward' => 'true',
        ];

        $urlWildcardCreate = $this->getParser();
        $result = $urlWildcardCreate->parse($inputArray, $this->getParsingDispatcherMock());

        self::assertEquals(
            [
                'sourceUrl' => '/source/url',
                'destinationUrl' => '/destination/url',
                'forward' => true,
            ],
            $result,
            'URLWildcardCreate not parsed correctly.'
        );
    }

    public function testParseExceptionOnMissingSourceUrl(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'sourceUrl\' value for URLWildcardCreate.');
        $inputArray = [
            'destinationUrl' => '/destination/url',
            'forward' => 'true',
        ];

        $urlWildcardCreate = $this->getParser();
        $urlWildcardCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnMissingDestinationUrl(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'destinationUrl\' value for URLWildcardCreate.');
        $inputArray = [
            'sourceUrl' => '/source/url',
            'forward' => 'true',
        ];

        $urlWildcardCreate = $this->getParser();
        $urlWildcardCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnMissingForward(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'forward\' value for URLWildcardCreate.');
        $inputArray = [
            'sourceUrl' => '/source/url',
            'destinationUrl' => '/destination/url',
        ];

        $urlWildcardCreate = $this->getParser();
        $urlWildcardCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    protected function internalGetParser(): URLWildcardCreate
    {
        $parser = new URLWildcardCreate($this->getParserTools());
        $parser->setUriParser($this->getUriParserMock());

        return $parser;
    }
}
