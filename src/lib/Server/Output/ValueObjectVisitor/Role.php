<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\User\Role as ApiRole;
use Ibexa\Contracts\Core\Repository\Values\User\RoleDraft;
use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;
use Ibexa\Rest\Server\Values\RestRole;

/**
 * Role value object visitor.
 */
class Role extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param ApiRole|\Ibexa\Contracts\Core\Repository\Values\User\RoleDraft $data
     */
    public function visit(Visitor $visitor, Generator $generator, mixed $data): void
    {
        $generator->startObjectElement('Role');
        $visitor->setHeader(
            'Content-Type',
            $generator->getMediaType($data instanceof RoleDraft ? 'RoleDraft' : 'Role'),
        );
        $visitor->setHeader('Accept-Patch', $generator->getMediaType('RoleInput'));
        $this->visitRoleAttributes($generator, $data);
        $generator->endObjectElement('Role');
    }

    protected function visitRoleAttributes(Generator $generator, ApiRole|RoleDraft $data): void
    {
        $generator->startAttribute(
            'href',
            $this->router->generate('ibexa.rest.load_role', ['roleId' => $data->id])
        );
        $generator->endAttribute('href');

        $generator->startValueElement('identifier', $data->identifier);
        $generator->endValueElement('identifier');

        $generator->startObjectElement('Policies', 'PolicyList');
        $generator->startAttribute(
            'href',
            $this->router->generate('ibexa.rest.load_policies', ['roleId' => $data->id])
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('Policies');
    }
}
