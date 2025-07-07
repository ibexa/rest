<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Rest\Server\Input\Parser\ViewInputOneDotOne;
use Ibexa\Rest\Server\Values\RestViewInput;

class ViewInputOneDotOneTest extends BaseTest
{
    public function testParseContentQuery(): void
    {
        $inputArray = [
            'identifier' => 'Query identifier',
            'ContentQuery' => [],
        ];

        $parser = $this->getParser();
        $parsingDispatcher = $this->getParsingDispatcherMock();
        $parsingDispatcher
            ->expects(self::once())
            ->method('parse')
            ->with($inputArray['ContentQuery'], 'application/vnd.ibexa.api.internal.ContentQuery')
            ->willReturn(new Query());

        $result = $parser->parse($inputArray, $parsingDispatcher);

        $expectedViewInput = new RestViewInput();
        $expectedViewInput->identifier = 'Query identifier';
        $expectedViewInput->query = new Query();
        $expectedViewInput->languageCode = null;
        $expectedViewInput->useAlwaysAvailable = null;

        self::assertEquals($expectedViewInput, $result, 'RestViewInput not created correctly.');
    }

    public function testParseLocationQuery(): void
    {
        $inputArray = [
            'identifier' => 'Query identifier',
            'LocationQuery' => [],
        ];

        $parser = $this->getParser();
        $parsingDispatcher = $this->getParsingDispatcherMock();
        $parsingDispatcher
            ->expects(self::once())
            ->method('parse')
            ->with($inputArray['LocationQuery'], 'application/vnd.ibexa.api.internal.LocationQuery')
            ->willReturn(new LocationQuery());

        $result = $parser->parse($inputArray, $parsingDispatcher);

        $expectedViewInput = new RestViewInput();
        $expectedViewInput->identifier = 'Query identifier';
        $expectedViewInput->query = new LocationQuery();
        $expectedViewInput->languageCode = null;
        $expectedViewInput->useAlwaysAvailable = null;

        self::assertEquals($expectedViewInput, $result, 'RestViewInput not created correctly.');
    }

    public function testThrowsExceptionOnMissingIdentifier(): void
    {
        $this->expectException(Parser::class);
        $inputArray = ['Query' => []];
        $this->getParser()->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testThrowsExceptionOnMissingQuery(): void
    {
        $this->expectException(Parser::class);
        $inputArray = ['identifier' => 'foo'];
        $this->getParser()->parse($inputArray, $this->getParsingDispatcherMock());
    }

    protected function internalGetParser(): ViewInputOneDotOne
    {
        return new ViewInputOneDotOne();
    }
}
