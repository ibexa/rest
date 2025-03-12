<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Output\Normalizer;

use JsonSerializable;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class JsonSerializableNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param array<mixed> $context
     *
     * {@inheritDoc}
     */
    public function normalize($object, ?string $format = null, array $context = []): string|array
    {
        assert($object instanceof JsonSerializable);

        return $object->jsonSerialize();
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof JsonSerializable;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            JsonSerializable::class => true,
        ];
    }
}
