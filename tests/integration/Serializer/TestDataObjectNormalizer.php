<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\Rest\Serializer;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class TestDataObjectNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @return array<string, mixed>
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        assert($object instanceof TestDataObject);

        $data = [
            'string' => $object->string,
            'int' => $object->int,
            'location' => null,
        ];

        if ($object->apiLocation instanceof Location) {
            $normalizedLocation = $this->normalizer->normalize($object->apiLocation, $format);
            $data['location'] = $normalizedLocation['#'] ?? $normalizedLocation['Location'] ?? null;
        }

        return $data;
    }

    public function supportsNormalization(mixed $data, ?string $format = null): bool
    {
        return $data instanceof TestDataObject;
    }
}
