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
     *
     * {@inheritDoc}
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        $data = [];
        foreach ($object as $key => $value) {
            if (is_array($value)) {
                // If it's an array we assume that an array's first key is value that we have to store as a name of a parent element
                $parentKeyThatMustBeStored = array_key_first($value);
                $arrayCopy = $object->getArrayCopy();
                $reformattedArrayCopy = [];
                foreach ($arrayCopy as $arrayItem) {
                    $reformattedArrayCopy[] = $arrayItem[$parentKeyThatMustBeStored];
                }
                $data[$parentKeyThatMustBeStored] = $this->normalizer->normalize($reformattedArrayCopy, $format, $context);
            } else {
                $data[$key] = $this->normalizer->normalize($value, $format, $context);
            }
        }

        return $data;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof ArrayList;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            ArrayList::class => true,
        ];
    }
}
