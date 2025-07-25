<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\RelationType;
use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;

/**
 * RestRelation value object visitor.
 */
class RestRelation extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Rest\Server\Values\RestRelation $data
     */
    public function visit(Visitor $visitor, Generator $generator, mixed $data): void
    {
        $generator->startObjectElement('Relation');
        $visitor->setHeader('Content-Type', $generator->getMediaType('Relation'));

        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ibexa.rest.load_version_relation',
                [
                    'contentId' => $data->contentId,
                    'versionNumber' => $data->versionNo,
                    'relationId' => $data->relation->id,
                ]
            )
        );
        $generator->endAttribute('href');

        $generator->startObjectElement('SourceContent', 'ContentInfo');
        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ibexa.rest.load_content',
                [
                    'contentId' => $data->contentId,
                ]
            )
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('SourceContent');

        $generator->startObjectElement('DestinationContent', 'ContentInfo');
        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ibexa.rest.load_content',
                [
                    'contentId' => $data->relation->getDestinationContentInfo()->id,
                ]
            )
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('DestinationContent');

        if ($data->relation->sourceFieldDefinitionIdentifier !== null) {
            $generator->startValueElement('SourceFieldDefinitionIdentifier', $data->relation->sourceFieldDefinitionIdentifier);
            $generator->endValueElement('SourceFieldDefinitionIdentifier');
        }

        $generator->startValueElement('RelationType', $this->getRelationTypeString($data->relation->type));
        $generator->endValueElement('RelationType');

        $generator->endObjectElement('Relation');
    }

    /**
     * Returns $relationType as a readable string.
     *
     * @throws \Exception
     */
    protected function getRelationTypeString(int $relationType): string
    {
        $relationTypeList = [];

        if (RelationType::COMMON->value & $relationType) {
            $relationTypeList[] = 'COMMON';
        }
        if (RelationType::EMBED->value & $relationType) {
            $relationTypeList[] = 'EMBED';
        }
        if (RelationType::LINK->value & $relationType) {
            $relationTypeList[] = 'LINK';
        }
        if (RelationType::FIELD->value & $relationType) {
            $relationTypeList[] = 'ATTRIBUTE';
        }
        if (RelationType::ASSET->value & $relationType) {
            $relationTypeList[] = 'ASSET';
        }

        if (empty($relationTypeList)) {
            throw new \Exception('Unknown Relation type ' . $relationType . '.');
        }

        return implode(',', $relationTypeList);
    }
}
