<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Rest\Server\Input\Parser\ContentType\Criterion;

use Ibexa\Contracts\Core\Repository\Values\ContentType\Query\Criterion\ContentTypeIdentifier as ContentTypeIdentifierCriterion;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Server\Input\Parser\ContentType\Criterion\ContentTypeIdentifier;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ContentTypeIdentifierTest extends TestCase
{
    private ContentTypeIdentifier $parser;

    protected function setUp(): void
    {
        $this->parser = new ContentTypeIdentifier();
    }

    public function testValidInput(): void
    {
        self::assertEquals(
            new ContentTypeIdentifierCriterion(['article', 'blog_post']),
            $this->parser->parse(
                ['ContentTypeIdentifierCriterion' => ['article', 'blog_post']],
                $this->createStub(ParsingDispatcher::class)
            )
        );
    }

    /**
     * @phpstan-param array{
     *     array<string, string>
     * } $input
     */
    #[DataProvider('provideForTestInvalidInput')]
    public function testInvalidInput(string $exceptionMessage, array $input): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->parser->parse(
            $input,
            $this->createStub(ParsingDispatcher::class)
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
    public static function provideForTestInvalidInput(): iterable
    {
        yield [
            'Invalid <ContentTypeIdentifierCriterion>',
            [
                'bar' => 'foo',
            ],
        ];
    }
}
