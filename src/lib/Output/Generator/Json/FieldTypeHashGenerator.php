<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Output\Generator\Json;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FieldTypeHashGenerator implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private NormalizerInterface $normalizer;

    private bool $strictMode;

    public function __construct(
        NormalizerInterface $normalizer,
        ?LoggerInterface $logger = null,
        bool $strictMode = false
    ) {
        $this->normalizer = $normalizer;
        $this->logger = $logger ?? new NullLogger();
        $this->strictMode = $strictMode;
    }

    /**
     * Generates the field type value $hashValue as a child of the given Object
     * using $hashElementName as the property name.
     *
     * @param \Ibexa\Rest\Output\Generator\Json\ArrayObject|\Ibexa\Rest\Output\Generator\Json\JsonObject|\Ibexa\Rest\Output\Generator\Data\ArrayList $parent
     * @param string $hashElementName
     * @param mixed $hashValue
     */
    public function generateHashValue($parent, $hashElementName, $hashValue)
    {
        $parent->$hashElementName = $this->generateValue($parent, $hashValue);
    }

    /**
     * Generates and returns a value based on $hashValue type, with $parent (
     * if the type of $hashValue supports it).
     *
     * @param \Ibexa\Rest\Output\Generator\Json\ArrayObject|\Ibexa\Rest\Output\Generator\Json\JsonObject $parent
     * @param mixed $value
     *
     * @return mixed
     */
    protected function generateValue($parent, $value)
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

    /**
     * Generates and returns a JSON structure (array or object) depending on $value type
     * with $parent.
     *
     * If $type only contains numeric keys, the resulting structure will be an
     * JSON array, otherwise a JSON object
     *
     * @param \Ibexa\Rest\Output\Generator\Json\ArrayObject|\Ibexa\Rest\Output\Generator\Json\JsonObject $parent
     * @param array<mixed> $value
     *
     * @return \Ibexa\Rest\Output\Generator\Json\ArrayObject|\Ibexa\Rest\Output\Generator\Json\JsonObject
     */
    protected function generateArrayValue($parent, array $value)
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
     * @param \Ibexa\Rest\Output\Generator\Json\ArrayObject|\Ibexa\Rest\Output\Generator\Json\JsonObject|\Ibexa\Rest\Output\Generator\Data\ArrayList $parent
     * @param array<int> $listArray
     *
     * @return \Ibexa\Rest\Output\Generator\Json\ArrayObject|JsonObject
     */
    protected function generateListArray($parent, array $listArray)
    {
        $arrayObject = new ArrayObject($parent);
        foreach ($listArray as $listItem) {
            $arrayObject->append($this->generateValue($arrayObject, $listItem));
        }

        return $arrayObject;
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
        foreach ($hashArray as $hashKey => $hashItem) {
            $object->$hashKey = $this->generateValue($object, $hashItem);
        }

        return $object;
    }

    /**
     * Checks if the given $value is a purely numeric array.
     *
     * @param array $value
     *
     * @return bool
     */
    protected function isNumericArray(array $value)
    {
        foreach (array_keys($value) as $key) {
            if (is_string($key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param \Ibexa\Rest\Output\Generator\Json\ArrayObject|\Ibexa\Rest\Output\Generator\Json\JsonObject $parent
     *
     * @return mixed
     */
    protected function generateObjectValue($parent, object $value)
    {
        try {
            $value = $this->normalizer->normalize($value, 'json', ['parent' => $parent]);
        } catch (ExceptionInterface $e) {
            if ($this->strictMode) {
                throw $e;
            }
            $message = sprintf(
                'Unable to normalize value for type "%s". %s. '
                . 'Ensure that a normalizer is registered with tag: "%s".',
                get_class($value),
                $e->getMessage(),
                'ibexa.rest.serializer.normalizer',
            );
            $this->logger->error($message, [
                'exception' => $e,
            ]);

            $value = null;
        }

        if (is_array($value)) {
            return $this->generateArrayValue($parent, $value);
        }

        return $value;
    }
}
