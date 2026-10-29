<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\Visitor;
use Ibexa\Rest\Server\Values\RestFieldDefinition as ValuesRestFieldDefinition;

/**
 * FieldDefinitionList value object visitor.
 */
class FieldDefinitionList extends RestContentTypeBase
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Rest\Server\Values\FieldDefinitionList $data
     */
    public function visit(
        Visitor $visitor,
        Generator $generator,
        mixed $data
    ): void {
        $fieldDefinitionList = $data;
        $contentType = $fieldDefinitionList->contentType;

        $urlTypeSuffix = $this->getUrlTypeSuffix($contentType->status);

        $generator->startObjectElement('FieldDefinitions', 'FieldDefinitionList');
        $visitor->setHeader('Content-Type', $generator->getMediaType('FieldDefinitionList'));
        $visitor->suppressHeader('Accept-Patch');

        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ibexa.rest.load_content_type' . $urlTypeSuffix . '_field_definition_list',
                [
                    'contentTypeId' => $contentType->id,
                ]
            )
        );
        $generator->endAttribute('href');

        $generator->startList('FieldDefinition');
        foreach ($fieldDefinitionList->fieldDefinitions as $fieldDefinition) {
            $visitor->visitValueObject(
                new ValuesRestFieldDefinition($contentType, $fieldDefinition)
            );
        }
        $generator->endList('FieldDefinition');

        $generator->endObjectElement('FieldDefinitions');
    }
}
