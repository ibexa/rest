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
 * FieldDefinition list view model.
 */
class FieldDefinitionList extends RestValue
{
    /**
     * ContentType the field definitions belong to.
     *
     * @var ContentType
     */
    public $contentType;

    /**
     * Field definitions.
     *
     * @var FieldDefinition[]
     */
    public $fieldDefinitions;

    /**
     * Construct.
     *
     * @param ContentType $contentType
     * @param FieldDefinition[] $fieldDefinitions
     */
    public function __construct(
        ContentType $contentType,
        array $fieldDefinitions
    ) {
        $this->contentType = $contentType;
        $this->fieldDefinitions = $fieldDefinitions;
    }
}

class_alias(FieldDefinitionList::class, 'EzSystems\EzPlatformRest\Server\Values\FieldDefinitionList');
