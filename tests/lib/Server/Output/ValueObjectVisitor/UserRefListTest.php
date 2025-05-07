<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\Core\Repository\Values\User\User;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\RestUser;
use Ibexa\Rest\Server\Values\UserRefList;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class UserRefListTest extends ValueObjectVisitorBaseTest
{
    public function testVisit(): \DOMDocument
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $UserRefList = new UserRefList(
            [
                new RestUser(
                    new User(),
                    $this->getMockForAbstractClass(ContentType::class),
                    new ContentInfo(
                        [
                            'id' => 14,
                        ]
                    ),
                    new Location(),
                    []
                ),
            ],
            '/some/path'
        );

        $this->addRouteExpectation(
            'ibexa.rest.load_user',
            ['userId' => $UserRefList->users[0]->contentInfo->id],
            "/user/users/{$UserRefList->users[0]->contentInfo->id}"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $UserRefList
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
    public function testUserRefListHrefCorrect(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/UserRefList[@href="/some/path"]');
    }

    /**
     * @depends testVisit
     */
    public function testUserRefListMediaTypeCorrect(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/UserRefList[@media-type="application/vnd.ibexa.api.UserRefList+xml"]');
    }

    /**
     * @depends testVisit
     */
    public function testUserHrefCorrect(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/UserRefList/User[@href="/user/users/14"]');
    }

    /**
     * @depends testVisit
     */
    public function testUserMediaTypeCorrect(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/UserRefList/User[@media-type="application/vnd.ibexa.api.User+xml"]');
    }

    protected function internalGetVisitor(): ValueObjectVisitor\UserRefList
    {
        return new ValueObjectVisitor\UserRefList();
    }
}
