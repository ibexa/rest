<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Contracts\Rest\Exceptions;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException as APINotFoundException;

/**
 * REST API equivalent of PHP API's NotFoundException.
 *
 * Implementation of the {@see \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException}
 * interface.
 */
class NotFoundException extends APINotFoundException
{
}
