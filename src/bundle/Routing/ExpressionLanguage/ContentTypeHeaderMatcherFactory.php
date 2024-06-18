<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\Rest\Routing\ExpressionLanguage;

use Closure;
use Ibexa\Contracts\Rest\Input\MediaTypeParserInterface;
use Symfony\Component\HttpFoundation\Request;

final readonly class ContentTypeHeaderMatcherFactory
{
    public function __construct(
        private MediaTypeParserInterface $mediaTypeParser
    ) {
    }

    public function __invoke(): Closure
    {
        return function (Request $request): ?string {
            $contentTypeHeaderValue = $request->headers->get('Content-Type');

            if ($contentTypeHeaderValue === null) {
                return null;
            }

            return $this->mediaTypeParser->parseContentTypeHeader($contentTypeHeaderValue);
        };
    }
}
