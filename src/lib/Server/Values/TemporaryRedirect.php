<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Rest\Value as RestValue;

class TemporaryRedirect extends RestValue
{
    public string $redirectUri;

    public function __construct(string $redirectUri)
    {
        $this->redirectUri = $redirectUri;
    }
}
