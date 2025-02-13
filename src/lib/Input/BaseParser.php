<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Input;

use Ibexa\Contracts\Rest\Input\Parser;
use Ibexa\Contracts\Rest\UriParser\UriParserInterface;

abstract class BaseParser extends Parser
{
    protected UriParserInterface $uriParser;

    public function setUriParser(UriParserInterface $uriParser): void
    {
        $this->uriParser = $uriParser;
    }
}
