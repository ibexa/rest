<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;

/**
 * RoleList value object visitor.
 */
class RoleList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Rest\Server\Values\RoleList $data
     */
    public function visit(
        Visitor $visitor,
        Generator $generator,
        mixed $data
    ): void {
        $generator->startObjectElement('RoleList');
        $visitor->setHeader('Content-Type', $generator->getMediaType('RoleList'));
        $visitor->suppressHeader('Accept-Patch');

        $generator->startAttribute('href', $data->path);
        $generator->endAttribute('href');

        $generator->startList('Role');
        foreach ($data->roles as $role) {
            $visitor->visitValueObject($role);
        }
        $generator->endList('Role');

        $generator->endObjectElement('RoleList');
    }
}
