<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\Visitor;

/**
 * CreatedObjectStateGroup value object visitor.
 *
 * @todo coverage add unit test
 */
class CreatedObjectStateGroup extends ObjectStateGroup
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Contracts\Rest\Output\Visitor $visitor
     * @param \Ibexa\Contracts\Rest\Output\Generator $generator
     * @param \Ibexa\Rest\Server\Values\CreatedObjectStateGroup $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        parent::visit($visitor, $generator, $data->objectStateGroup);
        $visitor->setHeader(
            'Location',
            $this->router->generate(
                'ibexa.rest.load_object_state_group',
                ['objectStateGroupId' => $data->objectStateGroup->id]
            )
        );
        $visitor->setStatus(201);
    }
}

class_alias(CreatedObjectStateGroup::class, 'EzSystems\EzPlatformRest\Server\Output\ValueObjectVisitor\CreatedObjectStateGroup');
