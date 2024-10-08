<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Output\Normalizer;

use Ibexa\Rest\Output\Generator\Json\ArrayObject;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ArrayObjectNormalizer implements NormalizerInterface
{
    /**
     * @param array<mixed> $context
     *
     * @return array<mixed>
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $data = get_object_vars($object);

        foreach ($data as $key => $value) {
            $data[$key] = $this->normalize($value, $format, $context);
        }

        return $data;
    }

    public function supportsNormalization($data, ?string $format = null): bool
    {
        return $data instanceof ArrayObject;
    }
}
