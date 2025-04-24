<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\RestUserGroup;
use Ibexa\Rest\Server\Values\UserGroupList;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class UserGroupListTest extends ValueObjectVisitorBaseTest
{
    public function testVisit(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $userGroupList = new UserGroupList([], '/some/path');

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $userGroupList
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        return $result;
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsUserGroupListElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'UserGroupList',
            ],
            $result,
            'Invalid <UserGroupList> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsUserGroupListAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'UserGroupList',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.UserGroupList+xml',
                    'href' => '/some/path',
                ],
            ],
            $result,
            'Invalid <UserGroupList> attributes.',
            false
        );
    }

    public function testUserGroupListVisitsChildren(): void
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $userGroupList = new UserGroupList(
            [
                new RestUserGroup(
                    new Content(
                        [
                            'internalFields' => [],
                        ]
                    ),
                    $this->getMockForAbstractClass(ContentType::class),
                    new ContentInfo(),
                    new Location(),
                    []
                ),
                new RestUserGroup(
                    new Content(
                        [
                            'internalFields' => [],
                        ]
                    ),
                    $this->getMockForAbstractClass(ContentType::class),
                    new ContentInfo(),
                    new Location(),
                    []
                ),
            ],
            '/some/path'
        );

        $this->getVisitorMock()->expects(self::exactly(2))
            ->method('visitValueObject')
            ->with(self::isInstanceOf(RestUserGroup::class));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $userGroupList
        );
    }

    protected function internalGetVisitor(): ValueObjectVisitor\UserGroupList
    {
        return new ValueObjectVisitor\UserGroupList();
    }
}
