<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Output\Generator\InMemory;

use Ibexa\Rest\Output\Generator\Data;
use Ibexa\Rest\Output\Generator\Json;
use Ibexa\Rest\Output\Normalizer\ArrayListNormalizer;
use Ibexa\Rest\Output\Normalizer\ArrayObjectNormalizer;
use Ibexa\Rest\Output\Normalizer\JsonObjectNormalizer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class Xml extends Json
{
    public function getMediaType($name): string
    {
        return $this->generateMediaTypeWithVendor($name, 'xml', $this->vendor);
    }

    #[\Override]
    public function startList($name): void
    {
        $this->checkStartList($name);
        $array = new Data\ArrayList($name, $this->json);

        $this->json->$name = $array;
        $this->json = $array;
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

            $jsonValue->{'#'} = $value;
        }

        if ($this->json instanceof Json\ArrayObject) {
            $this->json->append($jsonValue);
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

        $data = $this->getData();

        if (!$data instanceof Json\JsonObject) {
            throw new \LogicException('Expected an instance of JsonObject');
        }

        $vars = get_object_vars($data);
        $encoderContext = $this->getEncoderContext($vars);
        $encoderContext['as_collection'] = true;

        $normalizers = [
            new ArrayListNormalizer(),
            new JsonObjectNormalizer(),
            new ArrayObjectNormalizer(),
            new ObjectNormalizer(),
        ];
        $encoders = [new XmlEncoder()];
        $serializer = new Serializer($normalizers, $encoders);

        return $serializer->serialize($data, 'xml', $encoderContext);
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        $this->transformData($data);

        return $data;
    }

    /**
     * @param array<mixed> $normalizedData
     *
     * @return array<mixed>
     */
    private function transformData(array $normalizedData): array
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

        return $this->clearEmptyArrays($data);
    }

    /**
     * @param array<mixed> $array
     *
     * @return array<mixed>
     */
    private function clearEmptyArrays(array &$array): array
    {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                // Recursively apply the function to the nested array
                $this->clearEmptyArrays($value);

                // Remove the field if it's an empty array after recursion
                if (empty($value)) {
                    unset($array[$key]);
                }
            }
        }

        return $array;
    }

    protected function getEncoderContext(array $data): array
    {
        return [
            XmlEncoder::ROOT_NODE_NAME => array_key_first($data),
        ];
    }
}
