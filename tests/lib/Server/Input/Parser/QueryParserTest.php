<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Rest\Server\Input\Parser\ContentQuery;
use Ibexa\Rest\Server\Input\Parser\ContentQuery as QueryParser;

class QueryParserTest extends BaseTestCase
{
    public function testParseEmptyQuery(): void
    {
        $inputArray = [
            'Filter' => [],
            'Query' => [],
        ];

        $parsingDispatcher = $this->getParsingDispatcherMock();
        $parser = $this->getParser();

        $result = $parser->parse($inputArray, $parsingDispatcher);

        $expectedQuery = new Query();

        self::assertEquals($expectedQuery, $result);
    }

    public function testDispatchOneFilter(): void
    {
        $inputArray = [
            'Filter' => ['ContentTypeIdentifierCriterion' => 'article'],
            'Query' => [],
        ];

        $parsingDispatcher = $this->getParsingDispatcherMock();
        $parsingDispatcher
            ->expects(self::once())
            ->method('parse')
            ->with(['ContentTypeIdentifierCriterion' => 'article'])
            ->willReturn(new Query\Criterion\ContentTypeIdentifier('article'));

        $parser = $this->getParser();

        $result = $parser->parse($inputArray, $parsingDispatcher);

        $expectedQuery = new Query();
        $expectedQuery->filter = new Query\Criterion\ContentTypeIdentifier('article');

        self::assertEquals($expectedQuery, $result);
    }

    public function testDispatchMoreThanOneFilter(): void
    {
        $inputArray = [
            'Filter' => ['ContentTypeIdentifierCriterion' => 'article', 'ParentLocationIdCriterion' => 762],
            'Query' => [],
        ];

        $parsingDispatcher = $this->getParsingDispatcherMock();
        $matcher = self::exactly(2);
        $parsingDispatcher
            ->expects($matcher)
            ->method('parse')
            ->willReturnCallback(static function (array $parameters) use ($matcher): Query\Criterion {
                $invocation = $matcher->numberOfInvocations();
                if ($invocation === 1) {
                    self::assertSame(['ContentTypeIdentifierCriterion' => 'article'], $parameters);

                    return new Query\Criterion\ContentTypeIdentifier('article');
                }
                if ($invocation === 2) {
                    self::assertSame(['ParentLocationIdCriterion' => 762], $parameters);

                    return new Query\Criterion\ParentLocationId(762);
                }

                self::fail('Unexpected call to parse().');
            });

        $parser = $this->getParser();

        $result = $parser->parse($inputArray, $parsingDispatcher);

        $expectedQuery = new Query();
        $expectedQuery->filter = new Query\Criterion\LogicalAnd([
            new Query\Criterion\ContentTypeIdentifier('article'),
            new Query\Criterion\ParentLocationId(762),
        ]);

        self::assertEquals($expectedQuery, $result);
    }

    public function testDispatchOneQueryItem(): void
    {
        $inputArray = [
            'Query' => ['ContentTypeIdentifierCriterion' => 'article'],
            'Filter' => [],
        ];

        $parsingDispatcher = $this->getParsingDispatcherMock();
        $parsingDispatcher
            ->expects(self::once())
            ->method('parse')
            ->with(['ContentTypeIdentifierCriterion' => 'article'])
            ->willReturn(new Query\Criterion\ContentTypeIdentifier('article'));

        $parser = $this->getParser();

        $result = $parser->parse($inputArray, $parsingDispatcher);

        $expectedQuery = new Query();
        $expectedQuery->query = new Query\Criterion\ContentTypeIdentifier('article');

        self::assertEquals($expectedQuery, $result);
    }

    public function testDispatchMoreThanOneQueryItem(): void
    {
        $inputArray = [
            'Query' => ['ContentTypeIdentifierCriterion' => 'article', 'ParentLocationIdCriterion' => 762],
            'Filter' => [],
        ];

        $parsingDispatcher = $this->getParsingDispatcherMock();
        $matcher = self::exactly(2);
        $parsingDispatcher
            ->expects($matcher)
            ->method('parse')
            ->willReturnCallback(static function (array $parameters) use ($matcher): Query\Criterion {
                $invocation = $matcher->numberOfInvocations();
                if ($invocation === 1) {
                    self::assertSame(['ContentTypeIdentifierCriterion' => 'article'], $parameters);

                    return new Query\Criterion\ContentTypeIdentifier('article');
                }
                if ($invocation === 2) {
                    self::assertSame(['ParentLocationIdCriterion' => 762], $parameters);

                    return new Query\Criterion\ParentLocationId(762);
                }

                self::fail('Unexpected call to parse().');
            });

        $parser = $this->getParser();

        $result = $parser->parse($inputArray, $parsingDispatcher);

        $expectedQuery = new Query();
        $expectedQuery->query = new Query\Criterion\LogicalAnd([
            new Query\Criterion\ContentTypeIdentifier('article'),
            new Query\Criterion\ParentLocationId(762),
        ]);

        self::assertEquals($expectedQuery, $result);
    }

    protected function internalGetParser(): ContentQuery
    {
        return new QueryParser();
    }
}
