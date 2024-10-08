<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Output\Normalizer;

use Ibexa\Rest\Output\Generator\Json\JsonObject;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class JsonObjectNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param array<mixed> $context
     *
     * @return array<mixed>
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $vars = get_object_vars($object);

        unset($vars['_ref_parent']);

        foreach ($vars as $name => $value) {
            $vars[$name] = $this->normalizer->normalize($value, $format, $context);
        }

        return $vars;
    }

    public function supportsNormalization($data, ?string $format = null): bool
    {
        return $data instanceof JsonObject;
    }
}
