<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Input;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\FieldTypeService;
use Ibexa\Rest\FieldTypeProcessorRegistry;

class FieldTypeParser
{
    protected ContentService $contentService;

    protected ContentTypeService $contentTypeService;

    protected FieldTypeService $fieldTypeService;

    protected FieldTypeProcessorRegistry $fieldTypeProcessorRegistry;

    public function __construct(
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        FieldTypeService $fieldTypeService,
        FieldTypeProcessorRegistry $fieldTypeProcessorRegistry
    ) {
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->fieldTypeService = $fieldTypeService;
        $this->fieldTypeProcessorRegistry = $fieldTypeProcessorRegistry;
    }

    /**
     * Parses the given $value for the field $fieldDefIdentifier in the content
     * identified by $contentInfoId.
     */
    public function parseFieldValue(int $contentInfoId, string $fieldDefIdentifier, mixed $value): mixed
    {
        $contentInfo = $this->contentService->loadContentInfo($contentInfoId);
        $contentType = $this->contentTypeService->loadContentType($contentInfo->contentTypeId);

        $fieldDefinition = $contentType->getFieldDefinition($fieldDefIdentifier);

        return $this->parseValue($fieldDefinition->getFieldTypeIdentifier(), $value);
    }

    /**
     * Parses the given $value using the FieldType identified by
     * $fieldTypeIdentifier.
     */
    public function parseValue(string $fieldTypeIdentifier, mixed $value): mixed
    {
        if ($this->fieldTypeProcessorRegistry->hasProcessor($fieldTypeIdentifier)) {
            $fieldTypeProcessor = $this->fieldTypeProcessorRegistry->getProcessor($fieldTypeIdentifier);
            $value = $fieldTypeProcessor->preProcessValueHash($value);
        }

        $fieldType = $this->fieldTypeService->getFieldType($fieldTypeIdentifier);

        return $fieldType->fromHash($value);
    }

    /**
     * Parses the given $settingsHash using the FieldType identified by
     * $fieldTypeIdentifier.
     */
    public function parseFieldSettings(string $fieldTypeIdentifier, mixed $settingsHash): mixed
    {
        if ($this->fieldTypeProcessorRegistry->hasProcessor($fieldTypeIdentifier)) {
            $fieldTypeProcessor = $this->fieldTypeProcessorRegistry->getProcessor($fieldTypeIdentifier);
            $settingsHash = $fieldTypeProcessor->preProcessFieldSettingsHash($settingsHash);
        }

        $fieldType = $this->fieldTypeService->getFieldType($fieldTypeIdentifier);

        return $fieldType->fieldSettingsFromHash($settingsHash);
    }

    /**
     * Parses the given $configurationHash using the FieldType identified by
     * $fieldTypeIdentifier.
     *
     * @param array<mixed> $configurationHash
     */
    public function parseValidatorConfiguration(string $fieldTypeIdentifier, array $configurationHash): mixed
    {
        if ($this->fieldTypeProcessorRegistry->hasProcessor($fieldTypeIdentifier)) {
            $fieldTypeProcessor = $this->fieldTypeProcessorRegistry->getProcessor($fieldTypeIdentifier);
            $configurationHash = $fieldTypeProcessor->preProcessValidatorConfigurationHash($configurationHash);
        }

        $fieldType = $this->fieldTypeService->getFieldType($fieldTypeIdentifier);

        return $fieldType->validatorConfigurationFromHash($configurationHash);
    }
}
