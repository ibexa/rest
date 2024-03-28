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
use Ibexa\Rest\Server\Values\RestUser;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class RestUserTest extends ValueObjectVisitorBaseTest
{
    /**
     * @return \DOMDocument
     */
    public function testVisitWithoutEmbeddedVersion()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $restUser = $this->getBasicRestUser();

        $this->getVisitorMock()->expects($this->once())
            ->method('visitValueObject');

        $locationPath = implode('/', $restUser->mainLocation->path);
        $this->addRouteExpectation(
            'ibexa.rest.load_user',
            ['userId' => $restUser->contentInfo->id],
            "/user/users/{$restUser->contentInfo->id}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_content_type',
            ['contentTypeId' => $restUser->contentInfo->contentTypeId],
            "/content/types/{$restUser->contentInfo->contentTypeId}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_content_versions',
            ['contentId' => $restUser->contentInfo->id],
            "/content/objects/{$restUser->contentInfo->id}/versions"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_section',
            ['sectionId' => $restUser->contentInfo->sectionId],
            "/content/sections/{$restUser->contentInfo->sectionId}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_location',
            ['locationPath' => $locationPath],
            "/content/locations/{$locationPath}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_locations_for_content',
            ['contentId' => $restUser->contentInfo->id],
            "/content/objects/{$restUser->contentInfo->id}/locations"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_user_groups_of_user',
            ['userId' => $restUser->contentInfo->id],
            "/user/users/{$restUser->contentInfo->id}/groups"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_user',
            ['userId' => $restUser->contentInfo->ownerId],
            "/user/users/{$restUser->contentInfo->ownerId}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_user_groups_of_user',
            ['userId' => $restUser->contentInfo->id],
            "/user/users/{$restUser->contentInfo->id}/groups"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_role_assignments_for_user',
            ['userId' => $restUser->contentInfo->id],
            "/user/users/{$restUser->contentInfo->id}/roles"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $restUser
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        $dom = new \DOMDocument();
        $dom->loadXml($result);

        return $dom;
    }

    protected function getBasicRestUser()
    {
        return new RestUser(
            new Values\User\User(),
            $this->getMockForAbstractClass(ContentType::class),
            new ContentInfo(
                [
                    'id' => 22,
                    'name' => 'Sindelfingen',
                    'sectionId' => 23,
                    'currentVersionNo' => 5,
                    'published' => true,
                    'ownerId' => 24,
                    'modificationDate' => new \DateTime('2012-09-05 15:27 Europe/Berlin'),
                    'publishedDate' => new \DateTime('2012-09-05 15:27 Europe/Berlin'),
                    'alwaysAvailable' => true,
                    'remoteId' => 'abc123',
                    'mainLanguageCode' => 'eng-US',
                    'mainLocationId' => 25,
                    'contentTypeId' => 26,
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
    public function testUserHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/User[@href="/user/users/22"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUserIdCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/User[@id="22"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUserMediaTypeWithoutVersionCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/User[@media-type="application/vnd.ibexa.api.User+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUserRemoteIdCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/User[@remoteId="abc123"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUserTypeHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/User/ContentType[@href="/content/types/26"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUserTypeMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/User/ContentType[@media-type="application/vnd.ibexa.api.ContentType+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testNameCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/User/name[text()="Sindelfingen"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testVersionsHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/User/Versions[@href="/content/objects/22/versions"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testVersionsMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/User/Versions[@media-type="application/vnd.ibexa.api.VersionList+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testSectionHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/User/Section[@href="/content/sections/23"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testSectionMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/User/Section[@media-type="application/vnd.ibexa.api.Section+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testMainLocationHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/User/MainLocation[@href="/content/locations/1/2/23"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testMainLocationMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/User/MainLocation[@media-type="application/vnd.ibexa.api.Location+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testLocationsHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/User/Locations[@href="/content/objects/22/locations"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testLocationsMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/User/Locations[@media-type="application/vnd.ibexa.api.LocationList+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testOwnerHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/User/Owner[@href="/user/users/24"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testOwnerMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/User/Owner[@media-type="application/vnd.ibexa.api.User+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testLastModificationDateCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/User/lastModificationDate[text()="2012-09-05T15:27:00+02:00"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testMainLanguageCodeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/User/mainLanguageCode[text()="eng-US"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testAlwaysAvailableCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/User/alwaysAvailable[text()="true"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUserGroupsHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/User/UserGroups[@href="/user/users/22/groups"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUserGroupsMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/User/UserGroups[@media-type="application/vnd.ibexa.api.UserGroupList+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testRolesHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/User/Roles[@href="/user/users/22/roles"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testRolesMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/User/Roles[@media-type="application/vnd.ibexa.api.RoleAssignmentList+xml"]');
    }

    /**
     * Get the User visitor.
     *
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\RestUser
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\RestUser();
    }
}

class_alias(RestUserTest::class, 'EzSystems\EzPlatformRest\Tests\Server\Output\ValueObjectVisitor\RestUserTest');
