<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Rest\Input;

final class MediaTypeParser implements MediaTypeParserInterface
{
    private const string MEDIA_TYPE_PATTERN = '/application\/vnd\.ibexa\.api\.([^.]+)\+/';

    public function parseContentTypeHeader(string $header): ?string
    {
        if (preg_match(self::MEDIA_TYPE_PATTERN, $header, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
