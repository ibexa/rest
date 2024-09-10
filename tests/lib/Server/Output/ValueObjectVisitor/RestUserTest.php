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
use Ibexa\Rest\Server\Values\RestUser;

class RestUserTest extends BaseContentValueObjectVisitorTestCase
{
    public function testVisitWithoutEmbeddedVersion(): DOMDocument
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $restUser = $this->getBasicRestUser();

        $this->getVisitorMock()->expects(self::once())
            ->method('visitValueObject');

        $this->addRouteExpectation(
            'ibexa.rest.load_user',
            ['userId' => $restUser->contentInfo->id],
            "/user/users/{$restUser->contentInfo->id}"
        );
        $this->addContentRouteExpectations($restUser->contentInfo, $restUser->mainLocation);
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

        self::assertNotNull($result);

        $dom = new DOMDocument();
        $dom->loadXml($result);

        return $dom;
    }

    protected function getBasicRestUser(): RestUser
    {
        return new RestUser(
            new Values\User\User([
                'login' => 'rest_user',
                'email' => 'rest_user@ibexa.co',
            ]),
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

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUserHrefCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/User[@href="/user/users/22"]');
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUserIdCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/User[@id="22"]');
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUserMediaTypeWithoutVersionCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/User[@media-type="application/vnd.ibexa.api.User+xml"]');
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUserRemoteIdCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/User[@remoteId="abc123"]');
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUserTypeHrefCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/User/ContentType[@href="/content/types/26"]');
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUserTypeMediaTypeCorrect(DOMDocument $dom)
    {
        $this->assertXPath($dom, '/User/ContentType[@media-type="application/vnd.ibexa.api.ContentType+xml"]');
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUserGroupsHrefCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/User/UserGroups[@href="/user/users/22/groups"]');
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testUserGroupsMediaTypeCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/User/UserGroups[@media-type="application/vnd.ibexa.api.UserGroupList+xml"]');
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testRolesHrefCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/User/Roles[@href="/user/users/22/roles"]');
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testRolesMediaTypeCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/User/Roles[@media-type="application/vnd.ibexa.api.RoleAssignmentList+xml"]');
    }

    /**
     * Get the User visitor.
     */
    protected function internalGetVisitor(): ValueObjectVisitor\RestUser
    {
        return new ValueObjectVisitor\RestUser();
    }

    protected function getXPathFirstElementName(): string
    {
        return 'User';
    }
}
