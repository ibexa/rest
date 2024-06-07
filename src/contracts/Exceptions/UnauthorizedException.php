<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Rest\Exceptions;

use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException as ApiUnauthorizedException;
use Throwable;

final class UnauthorizedException extends ApiUnauthorizedException
{
    public function __construct(
        string $message = '',
        int $code = 401,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
