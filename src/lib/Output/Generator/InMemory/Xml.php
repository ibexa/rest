<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Output\Generator\InMemory;

use Ibexa\Rest\Output\Generator\Json;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class Xml extends Json
{
    public function getMediaType($name): string
    {
        return $this->generateMediaTypeWithVendor($name, 'xml', $this->vendor);
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function startAttribute($name, $value): void
    {
        $this->checkStartAttribute($name);

        $this->json->{'@' . $name} = $value;
    }

    public function serializeBool($boolValue)
    {
        return $boolValue ? 'true' : 'false';
    }

    public function startValueElement(string $name, $value, array $attributes = []): void
    {
        $this->checkStartValueElement($name);

        if (empty($attributes)) {
            $jsonValue = $value;
        } else {
            $jsonValue = new Json\JsonObject($this->json);
            foreach ($attributes as $attributeName => $attributeValue) {
                $jsonValue->{'@' . $attributeName} = $attributeValue;
            }
            /** @phpstan-ignore-next-line */
            $jsonValue->{'#'} = $value;
        }

        if ($this->json instanceof Json\ArrayObject) {
            $this->json[] = $jsonValue;
        } else {
            $this->json->$name = $jsonValue;
        }
    }

    /**
     * End document.
     *
     * Returns the generated document as a string.
     */
    public function endDocument(mixed $data): string
    {
        parent::endDocument($data);

        $normalizedData = $this->toArray();

        $encoderContext = $this->getEncoderContext($normalizedData);
        $encoderContext['as_collection'] = true;
        $transformedData = $this->transformData($normalizedData);

        $serializer = new Serializer([new ObjectNormalizer()], [new XmlEncoder()]);

        return $serializer->encode($transformedData, 'xml', $encoderContext);
    }

    public function transformData(array $normalizedData): array
    {
        $topNodeName = array_key_first($normalizedData);
        $data = array_filter(
            $normalizedData[$topNodeName] ?? [],
            static fn (string $key): bool => str_starts_with($key, '@'),
            ARRAY_FILTER_USE_KEY,
        );

        if ($topNodeName !== null) {
            $data['#'] = $normalizedData[$topNodeName];
        }

        return $data;
    }

//    /**
//     * @param array<mixed> $array
//     *
//     * @return array<mixed>
//     */
//    private function clearEmptyArrays(array &$array): array
//    {
//        foreach ($array as $key => &$value) {
//            if (is_array($value)) {
//                // Recursively apply the function to the nested array
//                $this->clearEmptyArrays($value);
//
//                // Remove the field if it's an empty array after recursion
//                if (empty($value)) {
//                    unset($array[$key]);
//                }
//            }
//        }
//
//        return $array;
//    }

    public function getEncoderContext(array $data): array
    {
        return [
            XmlEncoder::ROOT_NODE_NAME => array_key_first($data),
        ];
    }
}
