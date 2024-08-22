<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Output\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

//TODO for testing purposes, to remove
final class TestNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function normalize($object, string $format = null, array $context = []): array
    {
        $result = [];

        Assert::isInstanceOf($object, TestData::class);

        $result['TestData'] = [
            'name' => $object->getName(),
            'Location' => $this->normalizer->normalize($object->getLocation())['Location'],
        ];

        return $result;
    }

    public function supportsNormalization(mixed $data, string $format = null): bool
    {
        return $data instanceof TestData;
    }
}
