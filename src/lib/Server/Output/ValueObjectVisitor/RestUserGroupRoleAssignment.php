<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\User\Limitation\RoleLimitation;
use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;

/**
 * RestUserGroupRoleAssignment value object visitor.
 */
class RestUserGroupRoleAssignment extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Rest\Server\Values\RestUserGroupRoleAssignment $data
     */
    public function visit(Visitor $visitor, Generator $generator, mixed $data): void
    {
        $generator->startObjectElement('RoleAssignment');
        $visitor->setHeader('Content-Type', $generator->getMediaType('RoleAssignment'));

        $roleAssignment = $data->roleAssignment;
        $role = $roleAssignment->getRole();

        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ibexa.rest.load_role_assignment_for_user_group',
                [
                    'groupPath' => trim((string)$data->id, '/'),
                    'roleId' => $role->id,
                ]
            )
        );
        $generator->endAttribute('href');

        $roleLimitation = $roleAssignment->getRoleLimitation();
        if ($roleLimitation instanceof RoleLimitation) {
            $this->visitLimitation($generator, $roleLimitation);
        }

        $generator->startObjectElement('Role');
        $generator->startAttribute(
            'href',
            $this->router->generate('ibexa.rest.load_role', ['roleId' => $role->id])
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('Role');

        $generator->endObjectElement('RoleAssignment');
    }
}
