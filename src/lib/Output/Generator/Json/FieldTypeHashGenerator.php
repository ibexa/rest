<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Output\Generator\Json;

use Ibexa\Rest\Output\Generator\AbstractFieldTypeHashGenerator;
use Ibexa\Rest\Output\Generator\Data\DataObjectInterface;

class FieldTypeHashGenerator extends AbstractFieldTypeHashGenerator
{
    protected function generateValue(DataObjectInterface $parent, mixed $value): mixed
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
        DataObjectInterface $parent,
        array $listArray,
    ): DataObjectInterface {
        $arrayObject = new ArrayObject($parent);
        foreach ($listArray as $listItem) {
            $arrayObject->append($this->generateValue($arrayObject, $listItem));
        }

        return $arrayObject;
    }

    protected function generateHashArray(
        DataObjectInterface $parent,
        array $hashArray,
    ): JsonObject {
        $object = new JsonObject($parent);
        foreach ($hashArray as $hashKey => $hashItem) {
            $object->$hashKey = $this->generateValue($object, $hashItem);
        }

        return $object;
    }
}
