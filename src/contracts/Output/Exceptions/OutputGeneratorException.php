<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Contracts\Rest\Output\Exceptions;

use RuntimeException;

/**
 * Invalid output generation.
 */
class OutputGeneratorException extends RuntimeException
{
    /**
     * Construct from an error message.
     */
    public function __construct(string $message)
    {
        parent::__construct(
            'Output visiting failed: ' . $message
        );
    }
}
