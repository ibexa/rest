<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Output\Generator\Xml;

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

    protected function generateArrayValue($parent, $value): JsonObject
    {
        if ($this->isNumericArray($value)) {
            return $this->generateListArray($parent, $value);
        } else {
            return $this->generateHashArray($parent, $value);
        }
    }

    protected function generateListArray($parent, array $listArray): JsonObject
    {
        $object = new JsonObject($parent);
        $object->value = [];

        foreach ($listArray as $listItem) {
            $object->value[] = [
                '#' => $this->generateValue($object, $listItem),
            ];
        }

        return $object;
    }

    protected function generateHashArray($parent, array $hashArray): JsonObject
    {
        $object = new JsonObject($parent);
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
