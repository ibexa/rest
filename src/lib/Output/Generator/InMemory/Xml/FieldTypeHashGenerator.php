<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Output\Generator\InMemory\Xml;

use Ibexa\Rest\Output\Generator\Json\FieldTypeHashGenerator as JsonFieldTypeHashGenerator;
use Ibexa\Rest\Output\Generator\Json\JsonObject;

final class FieldTypeHashGenerator extends JsonFieldTypeHashGenerator
{
    protected function generateValue($parent, $value): mixed
    {
        if ($value === null) {
            return null;
        } elseif (is_bool($value)) {
            return $value ? 'true' : 'false';
        } elseif (is_float($value)) {
            return sprintf('%F', $value);
        } elseif (is_array($value)) {
            return $this->generateArrayValue($parent, $value);
        } elseif (is_object($value)) {
            return $this->generateObjectValue($parent, $value);
        } else {
            return $value;
        }
    }

    protected function generateArrayValue($parent, $value)
    {
        if ($this->isNumericArray($value)) {
            return $this->generateListArray($parent, $value);
        } else {
            return $this->generateHashArray($parent, $value);
        }
    }

    protected function generateListArray($parent, array $listArray)
    {
        $object = new JsonObject($parent);

        /** @phpstan-ignore-next-line */
        $object->value = [];

        foreach ($listArray as $listItem) {
            $object->value[] = [
                '#' => $this->generateValue($object, $listItem),
            ];
        }

        return $object;
    }

    /**
     * Generates a JSON object from the given $hashArray with $parent.
     *
     * @param \Ibexa\Rest\Output\Generator\Json\ArrayObject|\Ibexa\Rest\Output\Generator\Json\JsonObject $parent
     * @param array<mixed> $hashArray
     *
     * @return \Ibexa\Rest\Output\Generator\Json\JsonObject
     */
    protected function generateHashArray($parent, array $hashArray)
    {
        $object = new JsonObject($parent);

        /** @phpstan-ignore-next-line */
        $object->value = [];

        foreach ($hashArray as $hashKey => $hashItem) {
            $object->value[] = [
                '@key' => $hashKey,
                '#' => $this->generateValue($object, $hashItem),
            ];
        }

        return $object;
    }
}
