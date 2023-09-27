<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Rest\Output\Generator\Xml;

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

    public function __construct(
        NormalizerInterface $normalizer,
        ?LoggerInterface $logger = null
    ) {
        $this->normalizer = $normalizer;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Generates the field type value $hashValue into the $writer creating an
     * element with $hashElementName as its parent.
     *
     * @param \XmlWriter $writer
     * @param string $hashElementName
     * @param mixed $hashValue
     */
    public function generateHashValue(\XMLWriter $writer, $hashElementName, $hashValue)
    {
        $this->generateValue($writer, $hashValue, null, $hashElementName);
    }

    /**
     * Generates $value into a serialized representation.
     *
     * @param \XmlWriter $writer
     * @param mixed $value
     * @param string|null $key
     * @param string $elementName
     */
    protected function generateValue(\XmlWriter $writer, $value, $key = null, $elementName = 'value')
    {
        if ($value === null) {
            $this->generateNullValue($writer, $key, $elementName);
        } elseif (is_bool($value)) {
            $this->generateBooleanValue($writer, $value, $key, $elementName);
        } elseif (is_int($value)) {
            $this->generateIntegerValue($writer, $value, $key, $elementName);
        } elseif (is_float($value)) {
            $this->generateFloatValue($writer, $value, $key, $elementName);
        } elseif (is_string($value)) {
            $this->generateStringValue($writer, $value, $key, $elementName);
        } elseif (is_array($value)) {
            $this->generateArrayValue($writer, $value, $key, $elementName);
        } elseif (is_object($value)) {
            $this->generateObjectValue($value, $writer, $key, $elementName);
        } else {
            throw new \Exception('Invalid type in Field value hash: ' . get_debug_type($value));
        }
    }

    /**
     * Generates an array value from $value.
     *
     * @param \XmlWriter $writer
     * @param array $value
     * @param string|null $key
     * @param string $elementName
     */
    protected function generateArrayValue(\XmlWriter $writer, $value, $key, $elementName = 'value')
    {
        if ($this->isNumericArray($value)) {
            $this->generateListArray($writer, $value, $key, $elementName);
        } else {
            $this->generateHashArray($writer, $value, $key, $elementName);
        }
    }

    /**
     * Generates $value as a hash of value items.
     *
     * @param \XmlWriter $writer
     * @param array $value
     * @param string|null $key
     * @param string $elementName
     */
    protected function generateHashArray(\XmlWriter $writer, $value, $key = null, $elementName = 'value')
    {
        $writer->startElement($elementName);
        $this->generateKeyAttribute($writer, $key);

        foreach ($value as $hashKey => $hashItemValue) {
            $this->generateValue($writer, $hashItemValue, $hashKey);
        }

        $writer->endElement();
    }

    /**
     * Generates $value as a list of value items.
     *
     * @param \XmlWriter $writer
     * @param array $value
     * @param string|null $key
     * @param string $elementName
     */
    protected function generateListArray(\XmlWriter $writer, $value, $key = null, $elementName = 'value')
    {
        $writer->startElement($elementName);
        $this->generateKeyAttribute($writer, $key);

        foreach ($value as $listItemValue) {
            $this->generateValue($writer, $listItemValue);
        }

        $writer->endElement();
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
     * Generates a null value.
     *
     * @param \XmlWriter $writer
     * @param string|null $key
     * @param string $elementName
     */
    protected function generateNullValue(\XmlWriter $writer, $key = null, $elementName = 'value')
    {
        $writer->startElement($elementName);
        $this->generateKeyAttribute($writer, $key);
        // @todo: xsi:type?
        $writer->endElement();
    }

    /**
     * Generates a boolean value.
     *
     * @param \XmlWriter $writer
     * @param bool $value
     * @param string|null $key
     * @param string $elementName
     */
    protected function generateBooleanValue(\XmlWriter $writer, $value, $key = null, $elementName = 'value')
    {
        $writer->startElement($elementName);
        $this->generateKeyAttribute($writer, $key);
        $writer->text($value ? 'true' : 'false');
        $writer->endElement();
    }

    /**
     * Generates a integer value.
     *
     * @param \XmlWriter $writer
     * @param int $value
     * @param string|null $key
     * @param string $elementName
     */
    protected function generateIntegerValue(\XmlWriter $writer, $value, $key = null, $elementName = 'value')
    {
        $writer->startElement($elementName);
        $this->generateKeyAttribute($writer, $key);
        $writer->text($value);
        $writer->endElement();
    }

    /**
     * Generates a float value.
     *
     * @param \XmlWriter $writer
     * @param float $value
     * @param string|null $key
     * @param string $elementName
     */
    protected function generateFloatValue(\XmlWriter $writer, $value, $key = null, $elementName = 'value')
    {
        $writer->startElement($elementName);
        $this->generateKeyAttribute($writer, $key);
        $writer->text(sprintf('%F', $value));
        $writer->endElement();
    }

    /**
     * Generates a string value.
     *
     * @param \XmlWriter $writer
     * @param string $value
     * @param string|null $key
     * @param string $elementName
     */
    protected function generateStringValue(\XmlWriter $writer, $value, $key = null, $elementName = 'value')
    {
        $writer->startElement($elementName);
        $this->generateKeyAttribute($writer, $key);
        $writer->text($value);
        $writer->endElement();
    }

    /**
     * Generates a key attribute with $key as the value, if $key is not null.
     *
     * @param \XmlWriter $writer
     * @param string|null $key
     */
    protected function generateKeyAttribute(\XmlWriter $writer, $key = null)
    {
        if ($key !== null) {
            $writer->startAttribute('key');
            $writer->text($key);
            $writer->endAttribute();
        }
    }

    private function generateObjectValue(object $value, \XmlWriter $writer, ?string $key, string $elementName): void
    {
        try {
            $value = $this->normalizer->normalize($value, 'xml');
        } catch (ExceptionInterface $e) {
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

        if (is_object($value)) {
            $this->generateNullValue($writer, $key, $elementName);

            return;
        }

        $this->generateValue($writer, $value, $key, $elementName);
    }
}

class_alias(FieldTypeHashGenerator::class, 'EzSystems\EzPlatformRest\Output\Generator\Xml\FieldTypeHashGenerator');
