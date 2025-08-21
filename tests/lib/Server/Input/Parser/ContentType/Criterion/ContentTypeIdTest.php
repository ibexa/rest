<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Rest\Server\Input\Parser\ContentType\Criterion;

use Ibexa\Contracts\Core\Repository\Values\ContentType\Query\Criterion\ContentTypeId as ContentTypeIdCriterion;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Server\Input\Parser\ContentType\Criterion\ContentTypeId;
use PHPUnit\Framework\TestCase;

final class ContentTypeIdTest extends TestCase
{
    private ContentTypeId $parser;

    protected function setUp(): void
    {
        $this->parser = new ContentTypeId();
    }

    public function testValidInput(): void
    {
        self::assertEquals(
            new ContentTypeIdCriterion([1, 5]),
            $this->parser->parse(
                ['ContentTypeIdCriterion' => [1, 5]],
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
            'Invalid <ContentTypeIdCriterion>',
            [
                'bar' => 'foo',
            ],
        ];
    }
}
