<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Exception\Converter;

use Ibexa\Contracts\Core\Repository\Exceptions\Exception as RepositoryException;
use Ibexa\Rest\Server\Output\ValueObjectVisitor\Exception;
use Throwable;

/**
 * @internal
 */
final class AggregateRepositoryExceptionConverter implements RepositoryExceptionConverterInterface
{
    /** @var iterable<RepositoryExceptionConverterInterface> */
    private iterable $converters;

    public function __construct(iterable $converters)
    {
        $this->converters = $converters;
    }

    public function convert(RepositoryException $exception): Throwable
    {
        foreach ($this->converters as $converter) {
            if (!$converter->supports($exception)) {
                continue;
            }

            return $converter->convert($exception);
        }

        return $exception;
    }

    public function supports(RepositoryException $exception): bool
    {
        return true;
    }
}
