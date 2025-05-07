<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser;

use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Rest\Server\Input\Parser\RelationCreate;

class RelationCreateTest extends BaseTest
{
    public function testParse(): void
    {
        $inputArray = [
            'Destination' => [
                '_href' => '/content/objects/42',
            ],
        ];

        $relationCreate = $this->getParser();
        $result = $relationCreate->parse($inputArray, $this->getParsingDispatcherMock());

        self::assertEquals(
            42,
            $result,
            'RelationCreate struct not parsed correctly.'
        );
    }

    public function testParseExceptionOnMissingDestination(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing or invalid \'Destination\' element for RelationCreate.');
        $inputArray = [];

        $relationCreate = $this->getParser();
        $relationCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnMissingDestinationHref(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'_href\' attribute for the Destination element in RelationCreate.');
        $inputArray = [
            'Destination' => [],
        ];

        $relationCreate = $this->getParser();
        $relationCreate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    protected function internalGetParser(): RelationCreate
    {
        $parser = new RelationCreate();
        $parser->setUriParser($this->getUriParserMock());

        return $parser;
    }

    public function getParseHrefExpectationsMap(): array
    {
        return [
            ['/content/objects/42', 'contentId', 42],
        ];
    }
}
