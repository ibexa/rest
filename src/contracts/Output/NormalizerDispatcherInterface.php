<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Rest\Output;

interface NormalizerDispatcherInterface
{
    public function supportsNormalization(mixed $data): bool;

    public function visit(mixed $data, Generator $generator);
}