<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\Rest\Routing\ExpressionLanguage;

use Closure;
use Ibexa\Bundle\Rest\Routing\ExpressionLanguage\ContentTypeHeaderMatcherFactory;
use Ibexa\Contracts\Rest\Input\MediaTypeParser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class ContentTypeHeaderMatcherFactoryTest extends TestCase
{
    private readonly Closure $contentTypeHeaderMatcher;

    protected function setUp(): void
    {
        $contentTypeHeaderMatcherFactory = new ContentTypeHeaderMatcherFactory(
            new MediaTypeParser(),
        );
        $this->contentTypeHeaderMatcher = $contentTypeHeaderMatcherFactory();
    }

    public function testGetMediaType(): void
    {
        $request = new Request();
        $request->headers->add([
            'Content-Type' => 'application/vnd.ibexa.api.CopyContentTypeInput+json',
        ]);

        $closure = $this->contentTypeHeaderMatcher;

        self::assertSame('CopyContentTypeInput', $closure($request));
    }

    public function testRequestContentTypeDoesNotMatchRoute(): void
    {
        $request = new Request();
        $request->headers->add([
            'Content-Type' => 'application.CreateContentTypeInput+xml',
        ]);

        $closure = $this->contentTypeHeaderMatcher;

        self::assertNull($closure($request));
    }
}
