<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;
use Ibexa\Rest\Server\Values;

/**
 * RoleAssignmentList value object visitor.
 */
class RoleAssignmentList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Rest\Server\Values\RoleAssignmentList $data
     */
    public function visit(Visitor $visitor, Generator $generator, mixed $data): void
    {
        $generator->startObjectElement('RoleAssignmentList');
        $visitor->setHeader('Content-Type', $generator->getMediaType('RoleAssignmentList'));

        $generator->startAttribute(
            'href',
            $data->isGroupAssignment ?
                $this->router->generate('ibexa.rest.load_role_assignments_for_user_group', ['groupPath' => $data->id]) :
                $this->router->generate('ibexa.rest.load_role_assignments_for_user', ['userId' => $data->id])
        );
        $generator->endAttribute('href');

        $generator->startList('RoleAssignment');
        foreach ($data->roleAssignments as $roleAssignment) {
            $visitor->visitValueObject(
                $data->isGroupAssignment ?
                    new Values\RestUserGroupRoleAssignment($roleAssignment, $data->id) :
                    new Values\RestUserRoleAssignment($roleAssignment, $data->id)
            );
        }
        $generator->endList('RoleAssignment');

        $generator->endObjectElement('RoleAssignmentList');
    }
}
