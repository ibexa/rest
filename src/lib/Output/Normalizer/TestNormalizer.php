<?php

namespace Ibexa\Rest\Output\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

final class TestNormalizer implements NormalizerInterface
{
    public function normalize($object, string $format = null, array $context = []): array
    {
        $result = [];

        Assert::isInstanceOf($object, TestData::class);

        $result['TestData'] = [
            'name' => $object->getName(),
        ];

        return $result;
    }

    public function supportsNormalization(mixed $data, string $format = null): bool
    {
        return $data instanceof TestData;
    }
}
