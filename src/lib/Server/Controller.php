<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server;

use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Rest\UriParser\UriParserInterface;
use Ibexa\Rest\Input\Dispatcher as InputDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

abstract class Controller
{
    protected InputDispatcher $inputDispatcher;

    protected RouterInterface $router;

    protected UriParserInterface $uriParser;

    protected Repository $repository;

    public function setInputDispatcher(InputDispatcher $inputDispatcher): void
    {
        $this->inputDispatcher = $inputDispatcher;
    }

    public function setRouter(RouterInterface $router): void
    {
        $this->router = $router;
    }

    public function setRepository(Repository $repository): void
    {
        $this->repository = $repository;
    }

    public function setUriParser(UriParserInterface $uriParser): void
    {
        $this->uriParser = $uriParser;
    }

    /**
     * Extracts the requested media type from $request.
     *
     * @todo refactor, maybe to a REST Request with an accepts('content-type') method
     */
    protected function getMediaType(Request $request): string
    {
        foreach ($request->getAcceptableContentTypes() as $mimeType) {
            if (preg_match('(^([a-z0-9-/.]+)\+.*$)', strtolower($mimeType), $matches)) {
                return $matches[1];
            }
        }

        return 'unknown/unknown';
    }
}
