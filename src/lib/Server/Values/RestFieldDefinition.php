<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Rest\Value as RestValue;

/**
 * RestFieldDefinition view model.
 */
class RestFieldDefinition extends RestValue
{
    public ContentType $contentType;

    public FieldDefinition $fieldDefinition;

    /**
     * Path which is used to fetch the list of field definitions.
     */
    public ?string $path;

    public function __construct(ContentType $contentType, FieldDefinition $fieldDefinition, ?string $path = null)
    {
        $this->contentType = $contentType;
        $this->fieldDefinition = $fieldDefinition;
        $this->path = $path;
    }
}
