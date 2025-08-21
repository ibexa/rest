<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Rest\Server\Input\Parser\ContentType\Criterion;

use Ibexa\Contracts\Core\Repository\Values\ContentType\Query\Criterion\ContainsFieldDefinitionId as ContainsFieldDefinitionIdCriterion;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Server\Input\Parser\ContentType\Criterion\ContainsFieldDefinitionId;
use PHPUnit\Framework\TestCase;

final class ContainsFieldDefinitionIdTest extends TestCase
{
    private ContainsFieldDefinitionId $parser;

    protected function setUp(): void
    {
        $this->parser = new ContainsFieldDefinitionId();
    }

    public function testValidInput(): void
    {
        self::assertEquals(
            new ContainsFieldDefinitionIdCriterion([1, 5]),
            $this->parser->parse(
                ['ContainsFieldDefinitionIdCriterion' => [1, 5]],
                $this->createMock(ParsingDispatcher::class)
            )
        );
    }

    /**
     * @dataProvider provideForTestInvalidInput
     *
     * @phpstan-param string $exceptionMessage
     * @phpstan-param array{
     *     array<string, string>
     * } $input
     */
    public function testInvalidInput(string $exceptionMessage, array $input): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->parser->parse(
            $input,
            $this->createMock(ParsingDispatcher::class)
        );
    }

    /**
     * @phpstan-return iterable<
     *     array{
     *         string,
     *         array<string, string>,
     *     },
     * >
     */
    public function provideForTestInvalidInput(): iterable
    {
        yield [
            'Invalid <ContainsFieldDefinitionIdCriterion>',
            [
                'bar' => 'foo',
            ],
        ];
    }
}
