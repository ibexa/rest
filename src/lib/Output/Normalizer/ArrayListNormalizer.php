<?php

namespace Ibexa\Rest\Output\Normalizer;

use Ibexa\Rest\Output\Generator\Data\ArrayList;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ArrayListNormalizer implements NormalizerInterface
{

    public function normalize($object, ?string $format = null, array $context = [])
    {
        dump($object);
    }

    public function supportsNormalization($data, ?string $format = null): bool
    {
        return $data instanceof ArrayList;
    }
}