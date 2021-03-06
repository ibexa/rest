<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\RestUserGroup;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class RestUserGroupTest extends ValueObjectVisitorBaseTest
{
    /**
     * @return \DOMDocument
     */
    public function testVisitWithoutEmbeddedVersion()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $restUserGroup = $this->getBasicRestUserGroup();

        $this->getVisitorMock()->expects($this->once())
            ->method('visitValueObject');

        $userGroupPath = implode('/', $restUserGroup->mainLocation->path);
        $this->addRouteExpectation(
            'ibexa.rest.load_user_group',
            ['groupPath' => $userGroupPath],
            "/user/groups/{$userGroupPath}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_content_type',
            ['contentTypeId' => $restUserGroup->contentInfo->contentTypeId],
            "/content/types/{$restUserGroup->contentInfo->contentTypeId}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_content_versions',
            ['contentId' => $restUserGroup->contentInfo->id],
            "/content/objects/{$restUserGroup->contentInfo->id}/versions"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_section',
            ['sectionId' => $restUserGroup->contentInfo->sectionId],
            "/content/sections/{$restUserGroup->contentInfo->sectionId}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_location',
            ['locationPath' => $userGroupPath],
            "/content/locations/{$userGroupPath}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_locations_for_content',
            ['contentId' => $restUserGroup->contentInfo->id],
            "/content/objects/{$restUserGroup->contentInfo->id}/locations"
        );
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

        $this->assertNotNull($result);

        $dom = new \DOMDocument();
        $dom->loadXml($result);

        return $dom;
    }

    protected function getBasicRestUserGroup()
    {
        return new RestUserGroup(
            new Values\User\UserGroup(),
            $this->getMockForAbstractClass(ContentType::class),
            new ContentInfo(
                [
                    'id' => 'content23',
                    'name' => 'Sindelfingen',
                    'sectionId' => 'section23',
                    'currentVersionNo' => 5,
                    'published' => true,
                    'ownerId' => 'user23',
                    'modificationDate' => new \DateTime('2012-09-05 15:27 Europe/Berlin'),
                    'publishedDate' => new \DateTime('2012-09-05 15:27 Europe/Berlin'),
                    'alwaysAvailable' => true,
                    'remoteId' => 'abc123',
                    'mainLanguageCode' => 'eng-US',
                    'mainLocationId' => 'location23',
                    'contentTypeId' => 'contentType23',
                ]
            ),
            new Values\Content\Location(
                [
                    'pathString' => '/1/2/23',
                    'path' => [1, 2, 23],
                ]
            ),
            []
        );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUserGroupHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup[@href="/user/groups/1/2/23"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUserGroupIdCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup[@id="content23"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUserGroupMediaTypeWithoutVersionCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup[@media-type="application/vnd.ibexa.api.UserGroup+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUserGroupRemoteIdCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup[@remoteId="abc123"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUserGroupTypeHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/ContentType[@href="/content/types/contentType23"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUserGroupTypeMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/ContentType[@media-type="application/vnd.ibexa.api.ContentType+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testNameCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/name[text()="Sindelfingen"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testVersionsHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/Versions[@href="/content/objects/content23/versions"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testVersionsMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/Versions[@media-type="application/vnd.ibexa.api.VersionList+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testSectionHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/Section[@href="/content/sections/section23"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testSectionMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/Section[@media-type="application/vnd.ibexa.api.Section+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testMainLocationHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/MainLocation[@href="/content/locations/1/2/23"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testMainLocationMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/MainLocation[@media-type="application/vnd.ibexa.api.Location+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testLocationsHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/Locations[@href="/content/objects/content23/locations"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testLocationsMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/Locations[@media-type="application/vnd.ibexa.api.LocationList+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testOwnerHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/Owner[@href="/user/users/user23"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testOwnerMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/Owner[@media-type="application/vnd.ibexa.api.User+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testLastModificationDateCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/lastModificationDate[text()="2012-09-05T15:27:00+02:00"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testMainLanguageCodeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/mainLanguageCode[text()="eng-US"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testAlwaysAvailableCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/alwaysAvailable[text()="true"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testParentUserGroupHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/ParentUserGroup[@href="/user/groups/1/2"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testSubgroupsHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/Subgroups[@href="/user/groups/1/2/23/subgroups"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUsersHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/Users[@href="/user/groups/1/2/23/users"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testRolesHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/Roles[@href="/user/groups/1/2/23/roles"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testParentUserGroupMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/ParentUserGroup[@media-type="application/vnd.ibexa.api.UserGroup+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testSubgroupsMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/Subgroups[@media-type="application/vnd.ibexa.api.UserGroupList+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUsersMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/Users[@media-type="application/vnd.ibexa.api.UserList+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testRolesMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/UserGroup/Roles[@media-type="application/vnd.ibexa.api.RoleAssignmentList+xml"]');
    }

    /**
     * Get the UserGroup visitor.
     *
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\RestUserGroup
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\RestUserGroup();
    }
}

class_alias(RestUserGroupTest::class, 'EzSystems\EzPlatformRest\Tests\Server\Output\ValueObjectVisitor\RestUserGroupTest');
