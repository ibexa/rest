<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser;

use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;
use Ibexa\Rest\Server\Values\ContentTypePostOperationValue;

final class CopyContentTypeInput extends BaseParser
{
    /**
     * @phpstan-param array{} $data
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): ContentTypePostOperationValue
    {
        return new ContentTypePostOperationValue('copy', null);
    }
}
