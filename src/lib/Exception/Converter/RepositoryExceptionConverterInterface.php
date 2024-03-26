<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Exception\Converter;

use Ibexa\Contracts\Core\Repository\Exceptions\Exception as RepositoryException;
use Throwable;

/**
 * Convert Repository exceptions to their corresponding REST ValueObjectVisitor instances.
 *
 * @internal
 */
interface RepositoryExceptionConverterInterface
{
    public function convert(RepositoryException $exception): Throwable;

    public function supports(RepositoryException $exception): bool;
}
