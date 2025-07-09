<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest;

use Ibexa\Contracts\Rest\FieldTypeProcessor;

/**
 * FieldTypeProcessorRegistry.
 */
class FieldTypeProcessorRegistry
{
    /**
     * Registered processors.
     *
     * @var \Ibexa\Contracts\Rest\FieldTypeProcessor[]
     */
    private array $processors = [];

    /**
     * @param \Ibexa\Contracts\Rest\FieldTypeProcessor[] $processors
     */
    public function __construct(array $processors = [])
    {
        foreach ($processors as $fieldTypeIdentifier => $processor) {
            $this->registerProcessor($fieldTypeIdentifier, $processor);
        }
    }

    /**
     * Registers $processor for $fieldTypeIdentifier.
     */
    public function registerProcessor(string $fieldTypeIdentifier, FieldTypeProcessor $processor): void
    {
        $this->processors[$fieldTypeIdentifier] = $processor;
    }

    /**
     * Returns if a processor is registered for $fieldTypeIdentifier.
     */
    public function hasProcessor(string $fieldTypeIdentifier): bool
    {
        return isset($this->processors[$fieldTypeIdentifier]);
    }

    /**
     * Returns the processor for $fieldTypeIdentifier.
     *
     * @throws \RuntimeException if not processor is registered for $fieldTypeIdentifier
     */
    public function getProcessor(string $fieldTypeIdentifier): FieldTypeProcessor
    {
        if (!$this->hasProcessor($fieldTypeIdentifier)) {
            throw new \RuntimeException(
                "Field Type processor for '{$fieldTypeIdentifier}' not found."
            );
        }

        return $this->processors[$fieldTypeIdentifier];
    }
}
