<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use DOMDocument;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\RestUserGroup;
use PHPUnit\Framework\Attributes\Depends;

class RestUserGroupTest extends BaseContentValueObjectVisitorTestCase
{
    public function testVisitWithoutEmbeddedVersion(): DOMDocument
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $restUserGroup = $this->getBasicRestUserGroup();

        $this->getVisitorMock()->expects(self::once())
            ->method('visitValueObject');

        $userGroupPath = trim($restUserGroup->mainLocation->getPathString(), '/');
        $this->addRouteExpectation(
            'ibexa.rest.load_user_group',
            ['groupPath' => $userGroupPath],
            "/user/groups/{$userGroupPath}"
        );
        $this->addContentRouteExpectations($restUserGroup->contentInfo, $restUserGroup->mainLocation);
        $this->addRouteExpectation(
            'ibexa.rest.load_user',
            ['userId' => $restUserGroup->contentInfo->ownerId],
            "/user/users/{$restUserGroup->contentInfo->ownerId}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_user_group',
            ['groupPath' => '1/2'],
            '/user/groups/1/2'
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_sub_user_groups',
            ['groupPath' => $userGroupPath],
            "/user/groups/{$userGroupPath}/subgroups"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_users_from_group',
            ['groupPath' => $userGroupPath],
            "/user/groups/{$userGroupPath}/users"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_role_assignments_for_user_group',
            ['groupPath' => $userGroupPath],
            "/user/groups/{$userGroupPath}/roles"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $restUserGroup
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        $dom = new DOMDocument();
        $dom->loadXml($result);

        return $dom;
    }

    protected function getBasicRestUserGroup(): RestUserGroup
    {
        return new RestUserGroup(
            new Values\User\UserGroup(),
            $this->getMockForAbstractClass(ContentType::class),
            $this->getContentInfoStub(),
            new Values\Content\Location(
                [
                    'pathString' => '/1/2/23',
                    'path' => [1, 2, 23],
                ]
            ),
            []
        );
    }

    #[Depends('testVisitWithoutEmbeddedVersion')]
    public function testUserGroupHrefCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/UserGroup[@href="/user/groups/1/2/23"]');
    }

    #[Depends('testVisitWithoutEmbeddedVersion')]
    public function testUserGroupIdCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/UserGroup[@id="22"]');
    }

    #[Depends('testVisitWithoutEmbeddedVersion')]
    public function testUserGroupMediaTypeWithoutVersionCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/UserGroup[@media-type="application/vnd.ibexa.api.UserGroup+xml"]');
    }

    #[Depends('testVisitWithoutEmbeddedVersion')]
    public function testUserGroupRemoteIdCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/UserGroup[@remoteId="abc123"]');
    }

    #[Depends('testVisitWithoutEmbeddedVersion')]
    public function testUserGroupTypeHrefCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/UserGroup/ContentType[@href="/content/types/26"]');
    }

    #[Depends('testVisitWithoutEmbeddedVersion')]
    public function testUserGroupTypeMediaTypeCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/UserGroup/ContentType[@media-type="application/vnd.ibexa.api.ContentType+xml"]');
    }

    #[Depends('testVisitWithoutEmbeddedVersion')]
    public function testParentUserGroupHrefCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/UserGroup/ParentUserGroup[@href="/user/groups/1/2"]');
    }

    #[Depends('testVisitWithoutEmbeddedVersion')]
    public function testSubgroupsHrefCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/UserGroup/Subgroups[@href="/user/groups/1/2/23/subgroups"]');
    }

    #[Depends('testVisitWithoutEmbeddedVersion')]
    public function testUsersHrefCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/UserGroup/Users[@href="/user/groups/1/2/23/users"]');
    }

    #[Depends('testVisitWithoutEmbeddedVersion')]
    public function testRolesHrefCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/UserGroup/Roles[@href="/user/groups/1/2/23/roles"]');
    }

    #[Depends('testVisitWithoutEmbeddedVersion')]
    public function testParentUserGroupMediaTypeCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/UserGroup/ParentUserGroup[@media-type="application/vnd.ibexa.api.UserGroup+xml"]');
    }

    #[Depends('testVisitWithoutEmbeddedVersion')]
    public function testSubgroupsMediaTypeCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/UserGroup/Subgroups[@media-type="application/vnd.ibexa.api.UserGroupList+xml"]');
    }

    #[Depends('testVisitWithoutEmbeddedVersion')]
    public function testUsersMediaTypeCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/UserGroup/Users[@media-type="application/vnd.ibexa.api.UserList+xml"]');
    }

    #[Depends('testVisitWithoutEmbeddedVersion')]
    public function testRolesMediaTypeCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/UserGroup/Roles[@media-type="application/vnd.ibexa.api.RoleAssignmentList+xml"]');
    }

    /**
     * Get the UserGroup visitor.
     */
    protected function internalGetVisitor(): ValueObjectVisitor\RestUserGroup
    {
        return new ValueObjectVisitor\RestUserGroup();
    }

    protected function getXPathFirstElementName(): string
    {
        return 'UserGroup';
    }
}
