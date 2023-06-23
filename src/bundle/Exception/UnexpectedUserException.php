<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\Rest\Exception;

use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;

final class UnexpectedUserException extends UnauthorizedException
{
}
