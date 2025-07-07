<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\Visitor;

/**
 * CreatedFieldDefinition value object visitor.
 *
 * @todo coverage add test
 */
class CreatedFieldDefinition extends RestFieldDefinition
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Rest\Server\Values\CreatedFieldDefinition $data
     */
    public function visit(Visitor $visitor, Generator $generator, mixed $data): void
    {
        $restFieldDefinition = $data->fieldDefinition;

        parent::visit($visitor, $generator, $restFieldDefinition);

        $draftUriPart = $this->getUrlTypeSuffix($restFieldDefinition->contentType->status);
        $visitor->setHeader(
            'Location',
            $this->router->generate(
                "ibexa.rest.load_content_type{$draftUriPart}_field_definition",
                [
                    'contentTypeId' => $restFieldDefinition->contentType->id,
                    'fieldDefinitionId' => $restFieldDefinition->fieldDefinition->id,
                ]
            )
        );
        $visitor->setStatus(201);
    }
}
