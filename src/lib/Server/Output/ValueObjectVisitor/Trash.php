<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\Visitor;

/**
 * Trash value object visitor.
 */
class Trash extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \EzSystems\EzPlatformRest\Output\Visitor $visitor
     * @param \EzSystems\EzPlatformRest\Output\Generator $generator
     * @param \EzSystems\EzPlatformRest\Server\Values\Trash $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('Trash');
        $visitor->setHeader('Content-Type', $generator->getMediaType('Trash'));
        $generator->startAttribute('href', $data->path);
        $generator->endAttribute('href');

        $generator->startList('TrashItem');

        foreach ($data->trashItems as $trashItem) {
            $visitor->visitValueObject($trashItem);
        }

        $generator->endList('TrashItem');
        $generator->endObjectElement('Trash');
    }
}

class_alias(Trash::class, 'EzSystems\EzPlatformRest\Server\Output\ValueObjectVisitor\Trash');
