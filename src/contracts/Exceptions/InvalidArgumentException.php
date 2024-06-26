<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Contracts\Rest\Exceptions;

use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException as APIInvalidArgumentException;

/**
 * This exception is thrown if a service method is called with an illegal or non appropriate value.
 */
class InvalidArgumentException extends APIInvalidArgumentException
{
}
