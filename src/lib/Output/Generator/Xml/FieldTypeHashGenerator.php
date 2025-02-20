<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Output\Generator\Xml;

use Ibexa\Rest\Output\Generator\AbstractFieldTypeHashGenerator;
use Ibexa\Rest\Output\Generator\Data\DataObjectInterface;
use Ibexa\Rest\Output\Generator\Json\JsonObject;

final class FieldTypeHashGenerator extends AbstractFieldTypeHashGenerator
{
    protected function generateValue(DataObjectInterface $parent, mixed $value): mixed
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

    protected function generateListArray(
        DataObjectInterface $parent,
        array $listArray,
    ): DataObjectInterface {
        $object = new JsonObject($parent);
        $object->value = [];

        foreach ($listArray as $listItem) {
            $object->value[] = [
                '#' => $this->generateValue($object, $listItem),
            ];
        }

        return $object;
    }

    protected function generateHashArray(
        DataObjectInterface $parent,
        array $hashArray,
    ): JsonObject {
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
