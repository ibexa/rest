<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Output;

use Ibexa\Contracts\Core\Repository\FieldType;
use Ibexa\Contracts\Core\Repository\FieldTypeService;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Rest\FieldTypeProcessorRegistry;

/**
 * Serializes FieldType related data for REST output.
 */
class FieldTypeSerializer
{
    protected FieldTypeService $fieldTypeService;

    protected FieldTypeProcessorRegistry $fieldTypeProcessorRegistry;

    public function __construct(FieldTypeService $fieldTypeService, FieldTypeProcessorRegistry $fieldTypeProcessorRegistry)
    {
        $this->fieldTypeService = $fieldTypeService;
        $this->fieldTypeProcessorRegistry = $fieldTypeProcessorRegistry;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function serializeContentFieldValue(Generator $generator, Field $field): void
    {
        $this->serializeValue(
            'fieldValue',
            $generator,
            $this->fieldTypeService->getFieldType(
                $field->getFieldTypeIdentifier()
            ),
            $field->getValue()
        );
    }

    /**
     * Serializes the $defaultValue for $fieldDefIdentifier through $generator.
     */
    public function serializeFieldDefaultValue(Generator $generator, string $fieldTypeIdentifier, mixed $defaultValue): void
    {
        $this->serializeValue(
            'defaultValue',
            $generator,
            $this->getFieldType($fieldTypeIdentifier),
            $defaultValue
        );
    }

    /**
     * Serializes $settings as fieldSettings for $fieldDefinition using
     * $generator.
     */
    public function serializeFieldSettings(Generator $generator, string $fieldTypeIdentifier, mixed $settings): void
    {
        $fieldType = $this->fieldTypeService->getFieldType($fieldTypeIdentifier);
        $hash = $fieldType->fieldSettingsToHash($settings);

        if ($this->fieldTypeProcessorRegistry->hasProcessor($fieldTypeIdentifier)) {
            $processor = $this->fieldTypeProcessorRegistry->getProcessor($fieldTypeIdentifier);
            $hash = $processor->postProcessFieldSettingsHash($hash);
        }

        $this->serializeHash('fieldSettings', $generator, $hash);
    }

    /**
     * Serializes $validatorConfiguration for $fieldDefinition using $generator.
     */
    public function serializeValidatorConfiguration(Generator $generator, string $fieldTypeIdentifier, mixed $validatorConfiguration): void
    {
        $fieldType = $this->fieldTypeService->getFieldType($fieldTypeIdentifier);
        $hash = $fieldType->validatorConfigurationToHash($validatorConfiguration);

        if ($this->fieldTypeProcessorRegistry->hasProcessor($fieldTypeIdentifier)) {
            $processor = $this->fieldTypeProcessorRegistry->getProcessor($fieldTypeIdentifier);
            $hash = $processor->postProcessValidatorConfigurationHash($hash);
        }

        $this->serializeHash('validatorConfiguration', $generator, $hash);
    }

    /**
     * Returns the field type with $fieldTypeIdentifier.
     */
    protected function getFieldType(string $fieldTypeIdentifier): FieldType
    {
        return $this->fieldTypeService->getFieldType(
            $fieldTypeIdentifier
        );
    }

    /**
     * Serializes the given $value for $fieldType with $generator into
     * $elementName.
     */
    protected function serializeValue(string $elementName, Generator $generator, FieldType $fieldType, mixed $value): void
    {
        $hash = $fieldType->toHash($value);

        $fieldTypeIdentifier = $fieldType->getFieldTypeIdentifier();
        if ($this->fieldTypeProcessorRegistry->hasProcessor($fieldTypeIdentifier)) {
            $processor = $this->fieldTypeProcessorRegistry->getProcessor($fieldTypeIdentifier);
            $hash = $processor->postProcessValueHash($hash);
        }

        $this->serializeHash($elementName, $generator, $hash);
    }

    /**
     * Serializes the given $hash with $generator into $elementName.
     */
    protected function serializeHash(string $elementName, Generator $generator, mixed $hash): void
    {
        $generator->generateFieldTypeHash($elementName, $hash);
    }
}
