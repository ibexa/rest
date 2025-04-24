<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\Core\Repository\Values\User\UserGroup;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\RestUserGroup;
use Ibexa\Rest\Server\Values\UserGroupRefList;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class UserGroupRefListTest extends ValueObjectVisitorBaseTest
{
    public function testVisit(): \DOMDocument
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $UserGroupRefList = new UserGroupRefList(
            [
                new RestUserGroup(
                    new UserGroup(),
                    $this->getMockForAbstractClass(ContentType::class),
                    new ContentInfo(),
                    new Location(
                        [
                            'pathString' => '/1/5/14',
                            'path' => [1, 5, 14],
                        ]
                    ),
                    []
                ),
                new RestUserGroup(
                    new UserGroup(),
                    $this->getMockForAbstractClass(ContentType::class),
                    new ContentInfo(),
                    new Location(
                        [
                            'pathString' => '/1/5/13',
                            'path' => [1, 5, 13],
                        ]
                    ),
                    []
                ),
            ],
            '/some/path',
            14
        );

        $groupPath = trim($UserGroupRefList->userGroups[0]->mainLocation->pathString, '/');
        $this->addRouteExpectation(
            'ibexa.rest.load_user_group',
            ['groupPath' => $groupPath],
            "/user/groups/{$groupPath}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.unassign_user_from_user_group',
            ['userId' => $UserGroupRefList->userId, 'groupPath' => 14],
            '/user/users/14/groups/14'
        );

        $groupPath = trim($UserGroupRefList->userGroups[1]->mainLocation->pathString, '/');
        $this->addRouteExpectation(
            'ibexa.rest.load_user_group',
            ['groupPath' => '1/5/13'],
            "/user/groups/{$groupPath}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.unassign_user_from_user_group',
            ['userId' => $UserGroupRefList->userId, 'groupPath' => 13],
            '/user/users/14/groups/13'
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $UserGroupRefList
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        $dom = new \DOMDocument();
        $dom->loadXml($result);

        return $dom;
    }

    /**
     * @depends testVisit
     */
    public function testUserGroupRefListHrefCorrect(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/UserGroupRefList[@href="/some/path"]');
    }

    /**
     * @depends testVisit
     */
    public function testUserGroupRefListMediaTypeCorrect(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/UserGroupRefList[@media-type="application/vnd.ibexa.api.UserGroupRefList+xml"]');
    }

    /**
     * @depends testVisit
     */
    public function testFirstUserGroupHrefCorrect(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/UserGroupRefList/UserGroup[1][@href="/user/groups/1/5/14"]');
    }

    /**
     * @depends testVisit
     */
    public function testFirstUserGroupMediaTypeCorrect(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/UserGroupRefList/UserGroup[1][@media-type="application/vnd.ibexa.api.UserGroup+xml"]');
    }

    /**
     * @depends testVisit
     */
    public function testFirstUserGroupUnassignHrefCorrect(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/UserGroupRefList/UserGroup[1]/unassign[@href="/user/users/14/groups/14"]');
    }

    /**
     * @depends testVisit
     */
    public function testFirstUserGroupUnassignMethodCorrect(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/UserGroupRefList/UserGroup[1]/unassign[@method="DELETE"]');
    }

    /**
     * @depends testVisit
     */
    public function testSecondUserGroupHrefCorrect(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/UserGroupRefList/UserGroup[2][@href="/user/groups/1/5/13"]');
    }

    /**
     * @depends testVisit
     */
    public function testSecondUserGroupMediaTypeCorrect(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/UserGroupRefList/UserGroup[2][@media-type="application/vnd.ibexa.api.UserGroup+xml"]');
    }

    /**
     * @depends testVisit
     */
    public function testSecondUserGroupUnassignHrefCorrect(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/UserGroupRefList/UserGroup[2]/unassign[@href="/user/users/14/groups/13"]');
    }

    /**
     * @depends testVisit
     */
    public function testSecondUserGroupUnassignMethodCorrect(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/UserGroupRefList/UserGroup[2]/unassign[@method="DELETE"]');
    }

    protected function internalGetVisitor(): ValueObjectVisitor\UserGroupRefList
    {
        return new ValueObjectVisitor\UserGroupRefList();
    }
}
