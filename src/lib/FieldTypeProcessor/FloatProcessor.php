<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\FieldTypeProcessor;

use Ibexa\Contracts\Rest\FieldTypeProcessor;

class FloatProcessor extends FieldTypeProcessor
{
    public function preProcessValueHash(mixed $incomingValueHash): mixed
    {
        if (is_numeric($incomingValueHash)) {
            $incomingValueHash = (float)$incomingValueHash;
        }

        return $incomingValueHash;
    }
}
