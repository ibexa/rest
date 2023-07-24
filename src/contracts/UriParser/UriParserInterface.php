<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Rest\UriParser;

use Symfony\Component\HttpFoundation\Request;

interface UriParserInterface
{
    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException If $attribute wasn't found in the matched URI attributes
     */
    public function getAttributeFromUri(string $uri, string $attribute, string $method = 'GET'): string;

    public function isRestRequest(Request $request): bool;

    public function hasRestPrefix(string $uri): bool;

    /**
     * @internal use getAttributeFromUri
     *
     * @return array<mixed> matched route configuration and parameters
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\InvalidArgumentException
     */
    public function matchUri(string $uri, string $method = 'GET'): array;
}
