<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Rest\Value as RestValue;

class SeeOther extends RestValue
{
    private string $redirectUri;

    public function __construct(string $redirectUri)
    {
        $this->redirectUri = $redirectUri;
    }

    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }
}
