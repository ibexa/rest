<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Rest\Value as RestValue;

class PermanentRedirect extends RestValue
{
    /**
     * Redirect URI.
     *
     * @var string
     */
    public $redirectUri;

    /**
     * Construct.
     *
     * @param string $redirectUri
     */
    public function __construct($redirectUri)
    {
        $this->redirectUri = $redirectUri;
    }
}
