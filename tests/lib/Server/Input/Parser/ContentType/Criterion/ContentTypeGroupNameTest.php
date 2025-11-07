<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Rest\Server\Input\Parser\ContentType\Criterion;

use Ibexa\Contracts\Core\Repository\Values\ContentType\Query\Criterion\ContentTypeGroupName as ContentTypeGroupNameCriterion;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Server\Input\Parser\ContentType\Criterion\ContentTypeGroupName;
use PHPUnit\Framework\TestCase;

final class ContentTypeGroupNameTest extends TestCase
{
    private ContentTypeGroupName $parser;

    protected function setUp(): void
    {
        $this->parser = new ContentTypeGroupName();
    }

    public function testValidInput(): void
    {
        self::assertEquals(
            new ContentTypeGroupNameCriterion([1, 5]),
            $this->parser->parse(
                ['ContentTypeGroupNameCriterion' => [1, 5]],
                $this->createMock(ParsingDispatcher::class)
            )
        );
    }

    /**
     * @dataProvider provideForTestInvalidInput
     *
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
            'Invalid <ContentTypeGroupNameCriterion>',
            [
                'bar' => 'foo',
            ],
        ];
    }
}
