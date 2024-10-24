<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Output\Generator\Json;

use Ibexa\Rest\Output\Generator\AbstractFieldTypeHashGenerator;
use Ibexa\Rest\Output\Generator\Data\ArrayList;

class FieldTypeHashGenerator extends AbstractFieldTypeHashGenerator
{
    protected function generateValue(JsonObject|ArrayObject|ArrayList $parent, mixed $value): mixed
    {
        if ($value === null || is_scalar($value)) {
            // Will be handled accordingly on serialization
            return $value;
        }

        if (is_array($value)) {
            return $this->generateArrayValue($parent, $value);
        }

        if (is_object($value)) {
            return $this->generateObjectValue($parent, $value);
        }

        throw new \Exception('Invalid type in Field value hash: ' . get_debug_type($value));
    }

    protected function generateListArray(
        JsonObject|ArrayObject|ArrayList $parent,
        array $listArray,
    ): JsonObject|ArrayObject|ArrayList {
        $arrayObject = new ArrayObject($parent);
        foreach ($listArray as $listItem) {
            $arrayObject->append($this->generateValue($arrayObject, $listItem));
        }

        return $arrayObject;
    }

    protected function generateHashArray(
        JsonObject|ArrayObject|ArrayList $parent,
        array $hashArray,
    ): JsonObject|ArrayObject|ArrayList {
        $object = new JsonObject($parent);
        foreach ($hashArray as $hashKey => $hashItem) {
            $object->$hashKey = $this->generateValue($object, $hashItem);
        }

        return $object;
    }
}
