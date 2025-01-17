<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Output\Normalizer;

use ArrayObject as NativeArrayObject;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class NativeArrayObjectNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param array<mixed> $context
     *
     * {@inheritDoc}
     */
    public function normalize($object, ?string $format = null, array $context = []): mixed
    {
        assert($object instanceof NativeArrayObject);

        return $object->count() === 0 ? $object : $this->normalizer->normalize($object, $format, $context);
    }

    public function supportsNormalization($data, ?string $format = null): bool
    {
        return $data instanceof NativeArrayObject;
    }
}
