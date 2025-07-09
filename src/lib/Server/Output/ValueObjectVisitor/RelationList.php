<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;
use Ibexa\Rest\Server\Values\RestRelation as ValuesRestRelation;

/**
 * RelationList value object visitor.
 */
class RelationList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Rest\Server\Values\RelationList $data
     */
    public function visit(Visitor $visitor, Generator $generator, mixed $data): void
    {
        $generator->startObjectElement('Relations', 'RelationList');
        $visitor->setHeader('Content-Type', $generator->getMediaType('RelationList'));

        $path = $data->path;
        if ($path === null) {
            $path = $this->router->generate(
                'ibexa.rest.load_version_relations',
                [
                    'contentId' => $data->contentId,
                    'versionNumber' => $data->versionNo,
                ]
            );
        }

        $generator->startAttribute('href', $path);
        $generator->endAttribute('href');

        $generator->startList('Relation');
        foreach ($data->relations as $relation) {
            $visitor->visitValueObject(new ValuesRestRelation($relation, $data->contentId, $data->versionNo));
        }
        $generator->endList('Relation');

        $generator->endObjectElement('Relations');
    }
}
