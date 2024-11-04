<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Output\Normalizer;

use Ibexa\Rest\Output\Generator\Data\ArrayList;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ArrayListNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param \Ibexa\Rest\Output\Generator\Data\ArrayList $object
     * @param array<mixed> $context
     *
     * @return array<mixed>
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $data = [];
        foreach ($object as $key => $value) {
            $data[$key] = $this->normalizer->normalize($value, $format, $context);
        }

        return $data;
    }

    public function supportsNormalization($data, ?string $format = null): bool
    {
        return $data instanceof ArrayList;
    }
}
