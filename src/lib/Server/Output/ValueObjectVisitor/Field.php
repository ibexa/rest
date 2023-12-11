<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;
use Ibexa\Rest\Output\FieldTypeSerializer;

final class Field extends ValueObjectVisitor
{
    private FieldTypeSerializer $fieldTypeSerializer;

    public function __construct(FieldTypeSerializer $fieldTypeSerializer)
    {
        $this->fieldTypeSerializer = $fieldTypeSerializer;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Field $data
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function visit(Visitor $visitor, Generator $generator, $data): void
    {
        $generator->startHashElement('field');

        $generator->startValueElement('id', $data->id);
        $generator->endValueElement('id');

        $generator->startValueElement('fieldDefinitionIdentifier', $data->fieldDefIdentifier);
        $generator->endValueElement('fieldDefinitionIdentifier');

        $generator->startValueElement('languageCode', $data->languageCode);
        $generator->endValueElement('languageCode');

        $generator->startValueElement('fieldTypeIdentifier', $data->fieldTypeIdentifier);
        $generator->endValueElement('fieldTypeIdentifier');

        $this->fieldTypeSerializer->serializeContentFieldValue(
            $generator,
            $data
        );

        $generator->endHashElement('field');
    }
}
