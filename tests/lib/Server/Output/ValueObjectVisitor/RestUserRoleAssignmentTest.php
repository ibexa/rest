<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Core\Repository\Values\User;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class RestUserRoleAssignmentTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the RestUserRoleAssignment visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $userRoleAssignment = new Values\RestUserRoleAssignment(
            new User\UserRoleAssignment(
                [
                    'role' => new User\Role(
                        [
                            'id' => 42,
                            'identifier' => 'some-role',
                        ]
                    ),
                ]
            ),
            14
        );

        $this->addRouteExpectation(
            'ibexa.rest.load_role_assignment_for_user',
            [
                'userId' => $userRoleAssignment->id,
                'roleId' => $userRoleAssignment->roleAssignment->role->id,
            ],
            "/user/users/{$userRoleAssignment->id}/roles/{$userRoleAssignment->roleAssignment->role->id}"
        );

        $this->addRouteExpectation(
            'ibexa.rest.load_role',
            ['roleId' => $userRoleAssignment->roleAssignment->role->id],
            "/user/roles/{$userRoleAssignment->roleAssignment->role->id}"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $userRoleAssignment
        );

        $result = $generator->endDocument(null);

        self::assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains RoleAssignment element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRoleAssignmentElement($result)
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
     * Test if result contains RoleAssignment element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRoleAssignmentAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'RoleAssignment',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.RoleAssignment+xml',
                    'href' => '/user/users/14/roles/42',
                ],
            ],
            $result,
            'Invalid <RoleAssignment> attributes.',
            false
        );
    }

    /**
     * Test if result contains Role element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRoleElement($result)
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
     * Test if result contains Role element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRoleAttributes($result)
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

    /**
     * Get the UserRoleAssignment visitor.
     *
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\RestUserRoleAssignment
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\RestUserRoleAssignment();
    }
}
