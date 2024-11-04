<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Output\Generator;

use Ibexa\Rest\Output\Generator\Data\DataObjectInterface;
use Ibexa\Rest\Output\Generator\Json\JsonObject;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

abstract class AbstractFieldTypeHashGenerator implements LoggerAwareInterface
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
     */
    public function generateHashValue(
        DataObjectInterface $parent,
        string $hashElementName,
        mixed $hashValue
    ): void {
        $parent->$hashElementName = $this->generateValue($parent, $hashValue);
    }

    /**
     * Generates and returns a JSON structure (array or object) depending on $value type
     * with $parent.
     *
     * If $type only contains numeric keys, the resulting structure will be an
     * JSON array, otherwise a JSON object
     *
     * @param array<mixed> $value
     */
    protected function generateArrayValue(
        DataObjectInterface $parent,
        array $value,
    ): DataObjectInterface {
        if ($this->isNumericArray($value)) {
            return $this->generateListArray($parent, $value);
        } else {
            return $this->generateHashArray($parent, $value);
        }
    }

    /**
     * Generates and returns a value based on $hashValue type, with $parent (
     * if the type of $hashValue supports it).
     */
    abstract protected function generateValue(DataObjectInterface $parent, mixed $value): mixed;

    /**
     * Checks if the given $value is a purely numeric array.
     *
     * @param array<mixed> $value
     */
    protected function isNumericArray(array $value): bool
    {
        foreach (array_keys($value) as $key) {
            if (is_string($key)) {
                return false;
            }
        }

        return true;
    }

    protected function generateObjectValue(DataObjectInterface $parent, object $value): mixed
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

            assert($this->logger instanceof LoggerInterface);
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

    /**
     * Generates a JSON array from the given $hashArray with $parent.
     *
     * @param array<int> $listArray
     */
    abstract protected function generateListArray(
        DataObjectInterface $parent,
        array $listArray,
    ): DataObjectInterface;

    /**
     * Generates a JSON object from the given $hashArray with $parent.
     *
     * @param array<mixed> $hashArray
     */
    abstract protected function generateHashArray(
        DataObjectInterface $parent,
        array $hashArray,
    ): JsonObject;
}
