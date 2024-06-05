<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Rest\Input;

use Ibexa\Contracts\Rest\Input\MediaTypeParser;
use Ibexa\Contracts\Rest\Input\MediaTypeParserInterface;
use PHPUnit\Framework\TestCase;

final class MediaTypeParserTest extends TestCase
{
    private readonly MediaTypeParserInterface $mediaTypeParser;

    protected function setUp(): void
    {
        $this->mediaTypeParser = new MediaTypeParser();
    }

    public function testParsingSuccesses(): void
    {
        $header = 'application/vnd.ibexa.api.CopyContentTypeInput+json';

        self::assertSame('CopyContentTypeInput', $this->mediaTypeParser->parseContentTypeHeader($header));
    }

    /**
     * @dataProvider providerForParsingFails
     */
    public function testParsingFails(string $header): void
    {
        self::assertNull($this->mediaTypeParser->parseContentTypeHeader($header));
    }

    /**
     * @return iterable<array<int, string>>
     */
    public function providerForParsingFails(): iterable
    {
        yield 'a' => ['application.CopyContentTypeInput+json'];
        yield 'b' => ['application.CopyContentTypeInput'];
        yield 'c' => ['CopyContentTypeInput+json'];
        yield 'd' => ['CopyContentTypeInput'];
    }
}
