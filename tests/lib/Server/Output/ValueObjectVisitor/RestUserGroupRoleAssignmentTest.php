<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Core\Repository\Values\User;
use Ibexa\Rest\Server\Output\ValueObjectVisitor\RestUserGroupRoleAssignment;
use Ibexa\Rest\Server\Values;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class RestUserGroupRoleAssignmentTest extends ValueObjectVisitorBaseTest
{
    public function testVisit(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $userGroupRoleAssignment = new Values\RestUserGroupRoleAssignment(
            new User\UserGroupRoleAssignment(
                [
                    'role' => new User\Role(
                        [
                            'id' => 42,
                            'identifier' => 'some-role',
                        ]
                    ),
                ]
            ),
            '/1/5/14'
        );

        $this->addRouteExpectation(
            'ibexa.rest.load_role_assignment_for_user_group',
            [
                'groupPath' => '1/5/14',
                'roleId' => $userGroupRoleAssignment->roleAssignment->role->id,
            ],
            "/user/groups/1/5/14/roles/{$userGroupRoleAssignment->roleAssignment->role->id}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_role',
            ['roleId' => $userGroupRoleAssignment->roleAssignment->role->id],
            "/user/roles/{$userGroupRoleAssignment->roleAssignment->role->id}"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $userGroupRoleAssignment
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        return $result;
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRoleAssignmentElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'RoleAssignment',
                'children' => [
                    'count' => 1,
                ],
            ],
            $result,
            'Invalid <RoleAssignment> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRoleAssignmentAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'RoleAssignment',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.RoleAssignment+xml',
                    'href' => '/user/groups/1/5/14/roles/42',
                ],
            ],
            $result,
            'Invalid <RoleAssignment> attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRoleElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Role',
            ],
            $result,
            'Invalid <Role> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRoleAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Role',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.Role+xml',
                    'href' => '/user/roles/42',
                ],
            ],
            $result,
            'Invalid <Role> attributes.',
            false
        );
    }

    protected function internalGetVisitor(): RestUserGroupRoleAssignment
    {
        return new RestUserGroupRoleAssignment();
    }
}
