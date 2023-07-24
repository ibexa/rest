<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\Rest\UriParser;

use Ibexa\Bundle\Rest\EventListener\RequestListener;
use Ibexa\Contracts\Rest\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Rest\UriParser\UriParserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

/**
 * @internal
 */
final class UriParser implements UriParserInterface
{
    /**
     * @internal rely on \Ibexa\Contracts\Rest\UriParser\UriParserInterface::isRestRequest
     * or \Ibexa\Contracts\Rest\UriParser\UriParserInterface::hasRestPrefix instead.
     *
     * @see \Ibexa\Contracts\Rest\UriParser\UriParserInterface::isRestRequest()
     * @see \Ibexa\Contracts\Rest\UriParser\UriParserInterface::hasRestPrefix()
     */
    public const DEFAULT_REST_PREFIX_PATTERN = '/^\/api\/[a-zA-Z0-9-_]+\/v\d+(\.\d+)?\//';

    private UrlMatcherInterface $urlMatcher;

    private string $restPrefixPattern;

    public function __construct(
        UrlMatcherInterface $urlMatcher,
        string $restPrefixPattern = self::DEFAULT_REST_PREFIX_PATTERN
    ) {
        $this->urlMatcher = $urlMatcher;
        $this->restPrefixPattern = $restPrefixPattern;
    }

    public function matchUri(string $uri, string $method = 'GET'): array
    {
        if (!$this->hasRestPrefix($uri)) {
            // keeping the original exception message for BC, otherwise could be more verbose
            throw new InvalidArgumentException("No route matched '$uri'");
        }

        $request = Request::create($uri, $method);

        $originalContext = $this->urlMatcher->getContext();
        $context = clone $originalContext;
        $context->fromRequest($request);
        $this->urlMatcher->setContext($context);

        try {
            return $this->urlMatcher->match($request->getPathInfo());
        } catch (MethodNotAllowedException $e) {
            // seems MethodNotAllowedException has no message set
            $allowedMethods = implode(', ', $e->getAllowedMethods());
            throw new InvalidArgumentException(
                "Method '$method' is not allowed for '$uri'. Allowed: [$allowedMethods]",
                $e->getCode(),
                $e
            );
        } catch (ResourceNotFoundException $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        } finally {
            $this->urlMatcher->setContext($originalContext);
        }
    }

    public function getAttributeFromUri(string $uri, string $attribute, string $method = 'GET'): string
    {
        $parsingResult = $this->matchUri($uri, $method);

        if (!isset($parsingResult[$attribute])) {
            throw new InvalidArgumentException("No attribute '$attribute' in route matched from $uri");
        }

        return (string)$parsingResult[$attribute];
    }

    public function isRestRequest(Request $request): bool
    {
        return $this->hasRestPrefix($request->getPathInfo());
    }

    public function hasRestPrefix(string $uri): bool
    {
        // this entire parser needs to be moved to ibexa/rest and RequestListener should use it instead
        return (bool)preg_match($this->restPrefixPattern, $uri);
    }
}
