<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Rest\Output;

use Ibexa\Rest\Output\Generator\AbstractFieldTypeHashGenerator;
use Ibexa\Rest\Output\Generator\Data;
use Ibexa\Rest\Output\Generator\Json;

/**
 * Output generator.
 */
abstract class Generator
{
    protected const string NULL_PARENT_ELEMENT_EXCEPTION_MESSAGE = 'Parent element at %s cannot be `null`.';

    /**
     * Keeps track if the document is still empty.
     */
    protected bool $isEmpty = true;

    /**
     * Generator creation stack.
     *
     * Use to check if it is OK to start / close the requested element in the
     * current state.
     */
    /** @var array<array{string, mixed, array<mixed>}> */
    protected array $stack = [];

    /**
     * If set to true, output will be formatted and indented.
     */
    protected bool $formatOutput = false;

    /**
     * Enables developer to modify REST response media type prefix.
     */
    private string $vendor;

    /**
     * Generator for field type hash values.
     */
    protected AbstractFieldTypeHashGenerator $fieldTypeHashGenerator;

    /**
     * Data structure which is build during visiting.
     */
    protected Data\DataObjectInterface $json;

    public function __construct(string $vendor)
    {
        $this->vendor = $vendor;
    }

    public function setFormatOutput(bool $formatOutput): void
    {
        $this->formatOutput = $formatOutput;
    }

    /**
     * Returns if the document is empty or already contains data.
     */
    public function isEmpty(): bool
    {
        return $this->isEmpty;
    }

    protected function getVendor(): string
    {
        return $this->vendor;
    }

    /**
     * Reset output visitor to a virgin state.
     */
    public function reset(): void
    {
        $this->stack = [];
        $this->isEmpty = true;
    }

    /**
     * Start document.
     */
    public function startDocument(mixed $data): void
    {
        $this->checkStartDocument($data);

        $this->isEmpty = true;

        $this->json = new Json\JsonObject();
    }

    /**
     * Check start document.
     */
    protected function checkStartDocument(mixed $data): void
    {
        if (count($this->stack)) {
            throw new Exceptions\OutputGeneratorException(
                'Starting a document may only be the very first operation.'
            );
        }

        $this->stack[] = ['document', $data, []];
    }

    /**
     * End document.
     */
    abstract public function endDocument(mixed $data): string;

    /**
     * Check end document.
     */
    protected function checkEndDocument(mixed $data): void
    {
        $this->checkEnd('document', $data);
    }

    /**
     * Start object element.
     */
    public function startObjectElement(string $name, ?string $mediaTypeName = null): void
    {
        $this->checkStartObjectElement($name);

        $this->isEmpty = false;

        $mediaTypeName ??= $name;

        $this->createObjectElement($name);

        $this->attribute('media-type', $this->getMediaType($mediaTypeName));
    }

    /**
     * End object element.
     */
    public function endObjectElement(string $name): void
    {
        $this->checkEndObjectElement($name);

        if ($this->json->getParent() === null) {
            throw new \LogicException(sprintf(
                self::NULL_PARENT_ELEMENT_EXCEPTION_MESSAGE,
                __METHOD__,
            ));
        }

        $this->json = $this->json->getParent();
    }

    /**
     * Check start object element.
     */
    protected function checkStartObjectElement(mixed $data): void
    {
        $this->checkStart('objectElement', $data, ['document', 'objectElement', 'hashElement', 'list']);

        $this->checkStack($data);
    }

    /**
     * Check end object element.
     */
    protected function checkEndObjectElement(mixed $data): void
    {
        $this->checkEnd('objectElement', $data);
    }

    /**
     * Start hash element.
     */
    public function startHashElement(string $name): void
    {
        $this->checkStartHashElement($name);

        $this->isEmpty = false;

        $this->createObjectElement($name);
    }

    /**
     * Check start hash element.
     */
    protected function checkStartHashElement(mixed $data): void
    {
        $this->checkStart('hashElement', $data, ['document', 'objectElement', 'hashElement', 'list']);

        $this->checkStack($data);
    }

    /**
     * End hash element.
     */
    public function endHashElement(string $name): void
    {
        $this->checkEndHashElement($name);

        if ($this->json->getParent() === null) {
            throw new \LogicException(sprintf(
                self::NULL_PARENT_ELEMENT_EXCEPTION_MESSAGE,
                __METHOD__,
            ));
        }

        $this->json = $this->json->getParent();
    }

    /**
     * Check end hash element.
     */
    protected function checkEndHashElement(mixed $data): void
    {
        $this->checkEnd('hashElement', $data);
    }

    /**
     * Generate value element with given $name and $value.
     */
    public function valueElement(string $name, mixed $value): void
    {
        $this->startValueElement($name, $value);
        $this->endValueElement($name);
    }

    /**
     * @phpstan-param scalar|null $value
     * @phpstan-param array<string, scalar|null> $attributes
     */
    abstract public function startValueElement(string $name, mixed $value, array $attributes = []): void;

    /**
     * Check start value element.
     */
    protected function checkStartValueElement(mixed $data): void
    {
        $this->checkStart('valueElement', $data, ['objectElement', 'hashElement', 'list']);
    }

    /**
     * End value element.
     */
    public function endValueElement(string $name): void
    {
        $this->checkEndValueElement($name);
    }

    /**
     * Check end value element.
     */
    protected function checkEndValueElement(mixed $data): void
    {
        $this->checkEnd('valueElement', $data);
    }

    /**
     * Start list.
     */
    abstract public function startList(string $name): void;

    /**
     * Check start list.
     */
    protected function checkStartList(mixed $data): void
    {
        $this->checkStart('list', $data, ['objectElement', 'hashElement']);
    }

    /**
     * End list.
     */
    public function endList(string $name): void
    {
        $this->checkEndList($name);

        if ($this->json->getParent() === null) {
            throw new \LogicException(sprintf(
                self::NULL_PARENT_ELEMENT_EXCEPTION_MESSAGE,
                __METHOD__,
            ));
        }

        $this->json = $this->json->getParent();
    }

    /**
     * Check end list.
     */
    protected function checkEndList(mixed $data): void
    {
        $this->checkEnd('list', $data);
    }

    /**
     * Generate attribute with given $name and $value.
     */
    public function attribute(string $name, mixed $value): void
    {
        $this->startAttribute($name, $value);
        $this->endAttribute($name);
    }

    /**
     * Start attribute.
     */
    abstract public function startAttribute(string $name, mixed $value): void;

    /**
     * Check start attribute.
     */
    protected function checkStartAttribute(mixed $data): void
    {
        $this->checkStart('attribute', $data, ['objectElement', 'hashElement']);
    }

    /**
     * End attribute.
     */
    public function endAttribute(string $name): void
    {
        $this->checkEndAttribute($name);
    }

    /**
     * Check end attribute.
     */
    protected function checkEndAttribute(mixed $data): void
    {
        $this->checkEnd('attribute', $data);
    }

    /**
     * Get media type.
     */
    abstract public function getMediaType(string $name): string;

    /**
     * Generates a media type from $name, $type and $vendor.
     */
    protected function generateMediaTypeWithVendor(
        string $name,
        string $type,
        string $vendor = 'vnd.ibexa.api'
    ): string {
        return "application/{$vendor}.{$name}+{$type}";
    }

    /**
     * Generates a generic representation of the scalar, hash or list given in
     * $hashValue into the document, using an element of $hashElementName as
     * its parent.
     */
    public function generateFieldTypeHash(string $hashElementName, mixed $hashValue): void
    {
        $this->fieldTypeHashGenerator->generateHashValue(
            $this->json,
            $hashElementName,
            $hashValue
        );
    }

    /**
     * Check close / end operation.
     *
     * @param array<string> $validParents
     */
    protected function checkStart(string $type, mixed $data, array $validParents): void
    {
        $lastTag = end($this->stack);

        if (!is_array($lastTag)) {
            throw new Exceptions\OutputGeneratorException(
                sprintf(
                    'Invalid start: Trying to open outside of a document.'
                )
            );
        }

        if (!in_array($lastTag[0], $validParents)) {
            throw new Exceptions\OutputGeneratorException(
                sprintf(
                    'Invalid start: Trying to open %s inside %s, valid parent nodes are: %s.',
                    $type,
                    $lastTag[0],
                    implode(', ', $validParents)
                )
            );
        }

        $this->stack[] = [$type, $data, []];
    }

    /**
     * Check close / end operation.
     */
    protected function checkEnd(string $type, mixed $data): void
    {
        $lastTag = array_pop($this->stack);

        if (!is_array($lastTag)) {
            throw new Exceptions\OutputGeneratorException(
                sprintf(
                    'Invalid close: Trying to close on empty stack.'
                )
            );
        }

        if ([$lastTag[0], $lastTag[1]] !== [$type, $data]) {
            throw new Exceptions\OutputGeneratorException(
                sprintf(
                    'Invalid close: Trying to close %s:%s, while last element was %s:%s.',
                    $type,
                    $data,
                    $lastTag[0],
                    $lastTag[1]
                )
            );
        }
    }

    /**
     * Serializes a boolean value.
     */
    abstract public function serializeBool(mixed $boolValue): bool|string;

    abstract protected function getData(): Data\DataObjectInterface;

    /**
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    abstract public function getEncoderContext(array $data): array;

    private function createObjectElement(string $name): void
    {
        $object = new Json\JsonObject($this->json);

        if ($this->json instanceof Json\ArrayObject || $this->json instanceof Data\ArrayList) {
            $this->json->append($object);
            if ($this->json instanceof Data\ArrayList) {
                $this->json->setName($name);
            }
            $this->json = $object;
        } else {
            $this->json->$name = $object;
            $this->json = $object;
        }
    }

    protected function checkStack(mixed $data): void
    {
        $last = count($this->stack) - 2;
        if ($this->stack[$last][0] !== 'list' && isset($this->stack[$last][2][$data])) {
            throw new Exceptions\OutputGeneratorException(
                "Element {$data} may only occur once inside {$this->stack[$last][0]}."
            );
        }
        $this->stack[$last][2][$data] = true;
    }
}
