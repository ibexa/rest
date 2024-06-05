<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Bundle\Rest\Routing\ExpressionLanguage;

use Closure;
use Ibexa\Bundle\Rest\Routing\ExpressionLanguage\ContentTypeHeaderMatcherFactory;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class ContentTypeHeaderMatcherFactoryTest extends TestCase
{
    private readonly ParsingDispatcher&MockObject $parsingDispatcher;

    private readonly Closure $contentTypeHeaderMatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parsingDispatcher = $this->createMock(ParsingDispatcher::class);
        $contentTypeHeaderMatcherFactory = new ContentTypeHeaderMatcherFactory($this->parsingDispatcher);
        $this->contentTypeHeaderMatcher = $contentTypeHeaderMatcherFactory();
    }

    public function testRequestContentTypeMatchesRoute(): void
    {
        $request = new Request();
        $request->headers->add([
            'Content-Type' => 'application/vnd.ibexa.api.CopyContentTypeInput+json',
        ]);

        $closure = $this->contentTypeHeaderMatcher;
        $this->mockParsingDispatcher('application/vnd.ibexa.api.CopyContentTypeInput');

        self::assertTrue($closure($request, 'application/vnd.ibexa.api.CopyContentTypeInput'));
    }

    public function testRequestContentTypeDoesNotMatchRoute(): void
    {
        $request = new Request();
        $request->headers->add([
            'Content-Type' => 'application/vnd.ibexa.api.CreateContentTypeInput+xml',
        ]);

        $closure = $this->contentTypeHeaderMatcher;
        $this->mockParsingDispatcher('application/vnd.ibexa.api.CreateContentTypeInput');

        self::assertFalse($closure($request, 'application/vnd.ibexa.api.CopyContentTypeInput'));
    }

    private function mockParsingDispatcher(string $returnedMediaType): void
    {
        $this->parsingDispatcher
            ->expects(self::once())
            ->method('fetchMediaTypeWithoutVersion')
            ->willReturn($returnedMediaType);
    }
}
