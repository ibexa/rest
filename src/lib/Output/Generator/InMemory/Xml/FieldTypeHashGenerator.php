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
