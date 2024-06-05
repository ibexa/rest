<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\Rest\RequestParser;

use Ibexa\Contracts\Rest\UriParser\UriParserInterface;
use Ibexa\Rest\RequestParser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * @deprecated use \Ibexa\Contracts\Rest\UriParser\UriParserInterface instead
 * @see \Ibexa\Contracts\Rest\UriParser\UriParserInterface
 *
 * Router based request parser.
 */
class Router implements RequestParser
{
    private RouterInterface $router;

    private UriParserInterface $uriParser;

    public function __construct(RouterInterface $router, UriParserInterface $uriParser)
    {
        $this->router = $router;
        $this->uriParser = $uriParser;
    }

    /**
     * @return array<mixed> matched route configuration and parameters
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\InvalidArgumentException If no match was found
     */
    public function parse($url): array
    {
        return $this->uriParser->matchUri($url);
    }

    public function generate($type, array $values = [])
    {
        return $this->router->generate($type, $values);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException If $attribute wasn't found in the match
     */
    public function parseHref($href, $attribute)
    {
        return $this->uriParser->getAttributeFromUri($href, $attribute);
    }
}
