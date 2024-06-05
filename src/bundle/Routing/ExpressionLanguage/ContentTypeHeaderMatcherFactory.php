<?php

namespace Ibexa\Bundle\Rest\Routing\ExpressionLanguage;

use Closure;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Symfony\Component\HttpFoundation\Request;

final readonly class ContentTypeHeaderMatcherFactory
{
    public function __construct(
        private ParsingDispatcher $parsingDispatcher
    ) {
    }

    public function __invoke(): Closure
    {
        return function (Request $request, ?string $contentTypeHeaderToMatch): bool {
            $contentTypeHeaderValue = $request->headers->get('Content-Type');

            if ($contentTypeHeaderValue === null) {
                return false;
            }

            $mediaType = $this->parsingDispatcher->fetchMediaTypeWithoutVersion($contentTypeHeaderValue);

            return $contentTypeHeaderToMatch === $mediaType;
        };
    }
}