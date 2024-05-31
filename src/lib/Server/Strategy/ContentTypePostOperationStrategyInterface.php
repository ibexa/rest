<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Strategy;

use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Rest\Server\Values\ContentTypePostOperationValue;

interface ContentTypePostOperationStrategyInterface
{
    public function supports(ContentTypePostOperationValue $operation): bool;

    public function execute(ContentType $contentType): ContentType;
}
