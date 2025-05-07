<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Core\Repository\Values\User;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\RestUserRoleAssignment;
use Ibexa\Rest\Server\Values\RoleAssignmentList;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class RoleAssignmentListTest extends ValueObjectVisitorBaseTest
{
    public function testVisitUserRoleAssignmentList(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $roleAssignmentList = new RoleAssignmentList([], '42');

        $this->addRouteExpectation(
            'ibexa.rest.load_role_assignments_for_user',
            ['userId' => 42],
            '/user/users/42/roles'
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $roleAssignmentList
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        return $result;
    }

    /**
     * @depends testVisitUserRoleAssignmentList
     */
    public function testResultContainsRoleListElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'RoleAssignmentList',
            ],
            $result,
            'Invalid <RoleAssignmentList> element.',
            false
        );
    }

    /**
     * @depends testVisitUserRoleAssignmentList
     */
    public function testResultContainsUserRoleAssignmentListAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'RoleAssignmentList',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.RoleAssignmentList+xml',
                    'href' => '/user/users/42/roles',
                ],
            ],
            $result,
            'Invalid <RoleAssignmentList> attributes.',
            false
        );
    }

    public function testRoleAssignmentListVisitsChildren(): void
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $roleAssignmentList = new RoleAssignmentList(
            [
                new User\UserRoleAssignment(),
                new User\UserRoleAssignment(),
            ],
            42
        );

        $this->getVisitorMock()->expects(self::exactly(2))
             ->method('visitValueObject')
             ->with(self::isInstanceOf(RestUserRoleAssignment::class));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $roleAssignmentList
        );
    }

    public function testVisitGroupRoleAssignmentList(): string
    {
        $this->resetRouterMock();

        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $roleAssignmentList = new RoleAssignmentList([], '/1/5/777', true);

        $this->addRouteExpectation(
            'ibexa.rest.load_role_assignments_for_user_group',
            ['groupPath' => '/1/5/777'],
            '/user/groups/1/5/777/roles'
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $roleAssignmentList
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        return $result;
    }

    /**
     * @depends testVisitGroupRoleAssignmentList
     */
    public function testResultContainsGroupRoleAssignmentListAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'RoleAssignmentList',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.RoleAssignmentList+xml',
                    'href' => '/user/groups/1/5/777/roles',
                ],
            ],
            $result,
            'Invalid <RoleAssignmentList> attributes.',
            false
        );
    }

    protected function internalGetVisitor(): ValueObjectVisitor\RoleAssignmentList
    {
        return new ValueObjectVisitor\RoleAssignmentList();
    }
}
