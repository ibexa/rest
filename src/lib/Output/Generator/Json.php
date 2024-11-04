<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Output\Generator;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Rest\Output\Generator\Data\DataObjectInterface;

class Json extends Generator
{
    public function __construct(
        Json\FieldTypeHashGenerator $fieldTypeHashGenerator,
        string $vendor = 'vnd.ibexa.api',
    ) {
        $this->fieldTypeHashGenerator = $fieldTypeHashGenerator;
        $this->vendor = $vendor;
    }

    #[\Override]
    public function endDocument(mixed $data): string
    {
        parent::checkEndDocument($data);

        $jsonEncodeOptions = JSON_THROW_ON_ERROR;
        if ($this->formatOutput && defined('JSON_PRETTY_PRINT')) {
            $jsonEncodeOptions |= JSON_PRETTY_PRINT;
        }

        $data = $this->convertArrayObjects($this->json);

        return json_encode($data, $jsonEncodeOptions);
    }

    #[\Override]
    public function startValueElement(string $name, mixed $value, array $attributes = []): void
    {
        $this->checkStartValueElement($name);

        if (empty($attributes)) {
            $jsonValue = $value;
        } else {
            $jsonValue = new Json\JsonObject($this->json);
            foreach ($attributes as $attributeName => $attributeValue) {
                $jsonValue->{'_' . $attributeName} = $attributeValue;
            }
            $jsonValue->{'#text'} = $value;
        }

        if ($this->json instanceof Json\ArrayObject) {
            $this->json->append($jsonValue);
        } else {
            $this->json->$name = $jsonValue;
        }
    }

    #[\Override]
    public function startList(string $name): void
    {
        $this->checkStartList($name);

        $array = new Json\ArrayObject($this->json);

        $this->json->$name = $array;
        $this->json = $array;
    }

    #[\Override]
    public function startAttribute(string $name, mixed $value): void
    {
        $this->checkStartAttribute($name);

        $this->json->{'_' . $name} = $value;
    }

    #[\Override]
    public function getMediaType(string $name): string
    {
        return $this->generateMediaTypeWithVendor($name, 'json', $this->vendor);
    }

    #[\Override]
    public function serializeBool(mixed $boolValue): bool
    {
        return (bool)$boolValue;
    }

    #[\Override]
    public function getData(): DataObjectInterface
    {
        return $this->json;
    }

    #[\Override]
    public function getEncoderContext(array $data): array
    {
        return [];
    }

    /**
     * Convert ArrayObjects to arrays.
     *
     * Recursively convert all ArrayObjects into arrays in the full data
     * structure.
     */
    private function convertArrayObjects(mixed $data): mixed
    {
        if ($data instanceof Json\ArrayObject) {
            // @todo: Check if we need to convert arrays with only one single
            // element into non-arrays /cc cba
            $data = $data->getArrayCopy();
            foreach ($data as $key => $value) {
                $data[$key] = $this->convertArrayObjects($value);
            }
        } elseif ($data instanceof Json\JsonObject) {
            foreach ($data as $key => $value) {
                $data->$key = $this->convertArrayObjects($value);
            }
        }

        return $data;
    }
}
