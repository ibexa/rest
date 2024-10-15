<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Output\Generator\InMemory\Xml;

use Ibexa\Rest\Output\Generator\Data\ArrayList;
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
        } else {
            return $value;
        }
    }

    /**
     * Generates an array value from $value.
     *
     * @param array $value
     * @param string|null $key
     */
    protected function generateArrayValue($parent, $value)
    {
        if ($this->isNumericArray($value)) {
            return $this->generateListArray($parent, $value);
        } else {
            return $this->generateHashArray($parent, $value);
        }
    }

    /**
     * Generates a JSON array from the given $hashArray with $parent.
     *
     * @param \Ibexa\Rest\Output\Generator\Json\ArrayObject|\Ibexa\Rest\Output\Generator\Json\JsonObject $parent
     * @param array $listArray
     *
     * @return \Ibexa\Rest\Output\Generator\Json\ArrayObject
     */
    protected function generateListArray($parent, array $listArray)
    {
        $arrayList = new ArrayList('value', $parent);
        foreach ($listArray as $listItem) {
            $arrayList->append($this->generateValue($parent, $listItem));
        }

        return $arrayList;
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
