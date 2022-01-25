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
    /**
     * Test the RoleAssignmentList visitor.
     *
     * @return string
     */
    public function testVisitUserRoleAssignmentList()
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

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains RoleAssignmentList element.
     *
     * @param string $result
     *
     * @depends testVisitUserRoleAssignmentList
     */
    public function testResultContainsRoleListElement($result)
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
     * Test if result contains RoleAssignmentList element attributes.
     *
     * @param string $result
     *
     * @depends testVisitUserRoleAssignmentList
     */
    public function testResultContainsUserRoleAssignmentListAttributes($result)
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

    /**
     * Test if RoleAssignmentList visitor visits the children.
     */
    public function testRoleAssignmentListVisitsChildren()
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

        $this->getVisitorMock()->expects($this->exactly(2))
             ->method('visitValueObject')
             ->with($this->isInstanceOf(RestUserRoleAssignment::class));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $roleAssignmentList
        );
    }

    /**
     * Test the RoleAssignmentList visitor.
     *
     * @return string
     */
    public function testVisitGroupRoleAssignmentList()
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

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains RoleAssignmentList element attributes.
     *
     * @param string $result
     *
     * @depends testVisitGroupRoleAssignmentList
     */
    public function testResultContainsGroupRoleAssignmentListAttributes($result)
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

    /**
     * Get the RoleAssignmentList visitor.
     *
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\RoleAssignmentList
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\RoleAssignmentList();
    }
}

class_alias(RoleAssignmentListTest::class, 'EzSystems\EzPlatformRest\Tests\Server\Output\ValueObjectVisitor\RoleAssignmentListTest');
