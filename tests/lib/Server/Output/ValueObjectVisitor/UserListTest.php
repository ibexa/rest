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
use Ibexa\Rest\Server\Values\RestUser;
use Ibexa\Rest\Server\Values\UserList;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class UserListTest extends ValueObjectVisitorBaseTest
{
    public function testVisit(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $userList = new UserList([], '/some/path');

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $userList
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        return $result;
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsUserListElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'UserList',
            ],
            $result,
            'Invalid <UserList> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsUserListAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'UserList',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.UserList+xml',
                    'href' => '/some/path',
                ],
            ],
            $result,
            'Invalid <UserList> attributes.',
            false
        );
    }

    public function testUserListVisitsChildren(): void
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $userList = new UserList(
            [
                new RestUser(
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
                new RestUser(
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
            ->with(self::isInstanceOf(RestUser::class));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $userList
        );
    }

    protected function internalGetVisitor(): ValueObjectVisitor\UserList
    {
        return new ValueObjectVisitor\UserList();
    }
}
