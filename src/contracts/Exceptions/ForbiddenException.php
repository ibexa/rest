<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Contracts\Rest\Exceptions;

use InvalidArgumentException;

/**
 * Exception thrown if the request is forbidden.
 */
class ForbiddenException extends InvalidArgumentException
{
}
