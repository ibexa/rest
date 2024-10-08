<?php

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
        return get_object_vars($object);
    }

    public function supportsNormalization($data, ?string $format = null): bool
    {
        return $data instanceof ArrayObject;
    }
}