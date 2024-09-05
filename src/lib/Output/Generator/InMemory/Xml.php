<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Output\Generator\InMemory;

use Ibexa\Rest\Output\Generator\Json;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

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

    public function getEncoderContext(array $data): array
    {
        return [
            XmlEncoder::ROOT_NODE_NAME => array_key_first($data),
        ];
    }
}
