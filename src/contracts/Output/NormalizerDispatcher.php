<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Rest\Output;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class NormalizerDispatcher implements NormalizerDispatcherInterface
{
    public function __construct(
        private NormalizerInterface $normalizer
    ) {
    }

    public function supportsNormalization(mixed $data): bool
    {
        return $this->normalizer->supportsNormalization($data);
    }

    public function visit(mixed $data, Generator $generator): void
    {
        $normalizedData = $this->normalizer->normalize($data);

        $generator->setNormalizedData($normalizedData);
    }
}