<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\Rest\UriParser;

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

    private UrlMatcherInterface $restRouteMatcher;

    /**
     * @var string[]
     */
    private array $restPathPrefixes;

    /**
     * @param string[] $restPathPrefixes
     */
    public function __construct(
        UrlMatcherInterface $urlMatcher,
        string $restPrefixPattern = self::DEFAULT_REST_PREFIX_PATTERN,
        ?UrlMatcherInterface $restRouteMatcher = null,
        array $restPathPrefixes = []
    ) {
        $this->urlMatcher = $urlMatcher;
        $this->restPrefixPattern = $restPrefixPattern;
        $this->restRouteMatcher = $restRouteMatcher ?? $urlMatcher;
        $this->restPathPrefixes = array_map(
            static fn (string $restPathPrefix): string => rtrim($restPathPrefix, '/') . '/',
            $restPathPrefixes
        );
    }

    public function matchUri(string $uri, string $method = 'GET'): array
    {
        if (!$this->hasRestPrefix($uri)) {
            // keeping the original exception message for BC, otherwise could be more verbose
            throw new InvalidArgumentException("No route matched '$uri'");
        }

        $request = Request::create($uri, $method);

        try {
            return $this->matchRequest($this->urlMatcher, $request);
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
        $pathInfo = $request->getPathInfo();
        if (!$this->hasRestPrefix($pathInfo)) {
            return false;
        }

        return $this->hasRestPathPrefix($pathInfo) || $this->hasRestRoute($request);
    }

    public function hasRestPrefix(string $uri): bool
    {
        return (bool)preg_match($this->restPrefixPattern, $uri);
    }

    private function hasRestPathPrefix(string $pathInfo): bool
    {
        foreach ($this->restPathPrefixes as $restPathPrefix) {
            if (strpos($pathInfo, $restPathPrefix) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks whether a route exists for the request path, regardless of the HTTP method.
     */
    private function hasRestRoute(Request $request): bool
    {
        try {
            $this->matchRequest($this->restRouteMatcher, $request);
        } catch (MethodNotAllowedException $e) {
            // the route exists, it's up to the REST layer to respond with 405 Method Not Allowed
            return true;
        } catch (ResourceNotFoundException $e) {
            return false;
        }

        return true;
    }

    /**
     * Matches the request path in the context of the given request, as the matcher context might not have been
     * initialized from the current request yet (e.g. when called before the Router Listener).
     *
     * @return array<mixed> matched route configuration and parameters
     *
     * @throws \Symfony\Component\Routing\Exception\MethodNotAllowedException
     * @throws \Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    private function matchRequest(UrlMatcherInterface $urlMatcher, Request $request): array
    {
        $originalContext = $urlMatcher->getContext();
        $context = clone $originalContext;
        $context->fromRequest($request);
        $urlMatcher->setContext($context);

        try {
            return $urlMatcher->match($request->getPathInfo());
        } finally {
            $urlMatcher->setContext($originalContext);
        }
    }
}
