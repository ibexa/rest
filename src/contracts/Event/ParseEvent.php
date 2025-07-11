<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Rest\Event;

use Ibexa\Contracts\Core\Repository\Event\AfterEvent;

final class ParseEvent extends AfterEvent
{
    private mixed $valueObject;

    private array $data;

    private string $mediaType;

    public function __construct(
        mixed $valueObject,
        array $data,
        string $mediaType
    ) {
        $this->valueObject = $valueObject;
        $this->data = $data;
        $this->mediaType = $mediaType;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getMediaType(): string
    {
        return $this->mediaType;
    }

    public function getValueObject(): mixed
    {
        return $this->valueObject;
    }
}
