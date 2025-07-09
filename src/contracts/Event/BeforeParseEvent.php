<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Rest\Event;

use Ibexa\Contracts\Core\Repository\Event\BeforeEvent;
use UnexpectedValueException;

final class BeforeParseEvent extends BeforeEvent
{
    private array $data;

    private string $mediaType;

    private mixed $valueObject = null;

    public function __construct(
        array $data,
        string $mediaType,
    ) {
        $this->data = $data;
        $this->mediaType = $mediaType;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function getMediaType(): string
    {
        return $this->mediaType;
    }

    public function setMediaType(string $mediaType): void
    {
        $this->mediaType = $mediaType;
    }

    public function getValueObject(): mixed
    {
        if (!$this->hasValueObject()) {
            throw new UnexpectedValueException('Return value is not set. Check hasValueObject() or set it using setValueObject() before you call the getter.');
        }

        return $this->valueObject;
    }

    public function setValueObject(mixed $valueObject): void
    {
        $this->valueObject = $valueObject;
    }

    public function hasValueObject(): bool
    {
        return $this->valueObject !== null;
    }
}
