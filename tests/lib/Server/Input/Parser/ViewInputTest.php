<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Rest\Server\Input\Parser\ViewInput;
use Ibexa\Rest\Server\Values\RestViewInput;

class ViewInputTest extends BaseTest
{
    public function testParse(): void
    {
        $inputArray = [
            'identifier' => 'Query identifier',
            'Query' => [],
        ];

        $parser = $this->getParser();
        $parsingDispatcher = $this->getParsingDispatcherMock();
        $parsingDispatcher
            ->expects(self::once())
            ->method('parse')
            ->with($inputArray['Query'], 'application/vnd.ibexa.api.internal.ContentQuery')
            ->willReturn(new Query());

        $result = $parser->parse($inputArray, $parsingDispatcher);

        $expectedViewInput = new RestViewInput();
        $expectedViewInput->identifier = 'Query identifier';
        $expectedViewInput->query = new Query();
        $expectedViewInput->languageCode = null;
        $expectedViewInput->useAlwaysAvailable = null;

        self::assertEquals($expectedViewInput, $result, 'RestViewInput not created correctly.');
    }

    public function testThrowsExceptionOnMissingIdentifier(): void
    {
        $this->expectException('Ibexa\\Contracts\\Rest\\Exceptions\\Parser');
        $inputArray = ['Query' => []];
        $this->getParser()->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testThrowsExceptionOnMissingQuery(): void
    {
        $this->expectException('Ibexa\\Contracts\\Rest\\Exceptions\\Parser');
        $inputArray = ['identifier' => 'foo'];
        $this->getParser()->parse($inputArray, $this->getParsingDispatcherMock());
    }

    protected function internalGetParser(): ViewInput
    {
        return new ViewInput();
    }
}
