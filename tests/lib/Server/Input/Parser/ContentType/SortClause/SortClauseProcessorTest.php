<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Rest\Server\Input\Parser\ContentType\SortClause;

use Generator;
use Ibexa\Contracts\Core\Repository\Values\ContentType\Query\SortClause;
use Ibexa\Contracts\Core\Repository\Values\ContentType\Query\SortClause\Id;
use Ibexa\Contracts\Core\Repository\Values\ContentType\Query\SortClause\Identifier;
use Ibexa\Contracts\Rest\Input\Parser\Query\SortClause\SortClauseProcessorInterface;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Server\Input\Parser\ContentType\SortClause\SortClauseProcessor;
use Ibexa\Rest\Server\Input\Parser\SortClause\DataKeyValueObjectClass;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @template SC of object
 */
final class SortClauseProcessorTest extends TestCase
{
    /** @var \Ibexa\Contracts\Rest\Input\Parser\Query\SortClause\SortClauseProcessorInterface<SC> */
    private SortClauseProcessorInterface $sortClauseProcessor;

    protected function setUp(): void
    {
        $this->sortClauseProcessor = new SortClauseProcessor(
            $this->getParsingDispatcher()
        );
    }

    /**
     * @dataProvider provideForTestProcessSortClauses
     *
     * @param array<string, mixed> $inputClauses
     * @param array<\Ibexa\Contracts\Core\Repository\Values\ContentType\Query\SortClause> $expectedOutput
     */
    public function testProcessSortClauses(
        array $inputClauses,
        array $expectedOutput
    ): void {
        $generator = $this->sortClauseProcessor->processSortClauses($inputClauses);

        self::assertInstanceOf(
            Generator::class,
            $generator
        );

        self::assertEquals(
            $expectedOutput,
            iterator_to_array($generator)
        );
    }

    /**
     * @phpstan-return iterable<
     *     string,
     *     array{
     *         array<string, string>,
     *         array<\Ibexa\Contracts\Core\Repository\Values\ContentType\Query\SortClause>,
     *     },
     * >
     */
    public function provideForTestProcessSortClauses(): iterable
    {
        yield 'Input containing properly formatted clauses' => [
            [
                'Id' => 'ascending',
                'Identifier' => 'descending',
            ],
            [
                new Id(SortClause::SORT_ASC),
                new Identifier(SortClause::SORT_DESC),
            ],
        ];
    }

    private function getParsingDispatcher(): ParsingDispatcher
    {
        return new ParsingDispatcher(
            $this->createMock(EventDispatcherInterface::class),
            [
                'application/vnd.ibexa.api.internal.sortclause.Id' => new DataKeyValueObjectClass(
                    'Id',
                    Id::class
                ),
                'application/vnd.ibexa.api.internal.sortclause.Identifier' => new DataKeyValueObjectClass(
                    'Identifier',
                    Identifier::class
                ),
            ]
        );
    }
}
