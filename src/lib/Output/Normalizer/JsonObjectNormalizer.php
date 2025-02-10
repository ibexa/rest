<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Output\Normalizer;

use ArrayObject as NativeArrayObject;
use Ibexa\Rest\Output\Generator\Data\ArrayList;
use Ibexa\Rest\Output\Generator\Json\JsonObject;
use Ibexa\Rest\Output\Generator\Xml;
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
     *
     * {@inheritDoc}
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $vars = get_object_vars($object);

        $isOuterElement = $context[Xml::OUTER_ELEMENT] ?? false;
        unset($context[Xml::OUTER_ELEMENT]);

        $data = [];
        foreach ($vars as $key => $value) {
            if ($value instanceof ArrayList) {
                $name = $value->getName();
                if ($value->count() === 0) {
                    continue;
                }

                $normalizedData = $this->normalizer->normalize($value, $format, $context);

                if (is_array($normalizedData) && !array_is_list($normalizedData)) {
                    $innerElementKeyReplacingCurrent = array_key_first($normalizedData);
                    $data[$innerElementKeyReplacingCurrent] = $normalizedData[$innerElementKeyReplacingCurrent];
                } else {
                    $data[$name] = $normalizedData;
                }
            } else {
                $modifiedKey = $isOuterElement && count($vars) === 1 ? '#' : $key;
                $data[$modifiedKey] = $value instanceof NativeArrayObject
                    ? $value
                    : $this->normalizer->normalize($value, $format, $context)
                ;
            }
        }

        return $data;
    }

    public function supportsNormalization($data, ?string $format = null): bool
    {
        return $data instanceof JsonObject;
    }
}
