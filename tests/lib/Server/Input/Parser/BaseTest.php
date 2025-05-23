<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser;

use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Contracts\Rest\UriParser\UriParserInterface;
use Ibexa\Rest\Input;
use Ibexa\Tests\Rest\Server\BaseTest as ParentBaseTest;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Base test for input parsers.
 */
abstract class BaseTest extends ParentBaseTest
{
    protected ParsingDispatcher & MockObject $parsingDispatcherMock;

    protected UriParserInterface & MockObject $uriParserMock;

    protected Input\ParserTools $parserTools;

    protected function getParsingDispatcherMock(): ParsingDispatcher & MockObject
    {
        if (!isset($this->parsingDispatcherMock)) {
            $this->parsingDispatcherMock = $this->createMock(ParsingDispatcher::class);
        }

        return $this->parsingDispatcherMock;
    }

    /**
     * Returns the parseHref invocation expectations, as an array of:
     * 0. route to parse the href from (/content/objects/59
     * 1. attribute name we are looking for (contentId)
     * 2. expected return value (59)*.
     *
     * @return array
     */
    public function getParseHrefExpectationsMap()
    {
        return [];
    }

    protected function getUriParserMock(): UriParserInterface & MockObject
    {
        if (!isset($this->uriParserMock)) {
            $that = &$this;

            $callback = static function ($href, $attribute) use ($that): ?string {
                foreach ($that->getParseHrefExpectationsMap() as $map) {
                    if ($map[0] == $href && $map[1] == $attribute) {
                        if ($map[2] instanceof \Exception) {
                            throw $map[2];
                        } else {
                            return (string)$map[2];
                        }
                    }
                }

                return null;
            };

            $this->uriParserMock = $this->createMock(UriParserInterface::class);

            $this->uriParserMock
                ->expects(self::any())
                ->method('getAttributeFromUri')
                ->willReturnCallback($callback);
        }

        return $this->uriParserMock;
    }

    protected function getParserTools(): Input\ParserTools
    {
        if (!isset($this->parserTools)) {
            $this->parserTools = new Input\ParserTools();
        }

        return $this->parserTools;
    }

    protected function getParser(): Input\BaseParser
    {
        $parser = $this->internalGetParser();
        $parser->setUriParser($this->getUriParserMock());

        return $parser;
    }

    /**
     * Must return the tested parser object.
     */
    abstract protected function internalGetParser(): Input\BaseParser;
}
