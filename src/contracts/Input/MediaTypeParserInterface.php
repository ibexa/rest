<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Rest\Input;

interface MediaTypeParserInterface
{
    public function parseContentTypeHeader(string $header): ?string;
}
