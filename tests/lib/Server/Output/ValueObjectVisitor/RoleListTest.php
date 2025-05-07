<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\User\Role;
use Ibexa\Core\Repository\Values\User;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\RoleList;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class RoleListTest extends ValueObjectVisitorBaseTest
{
    public function testVisit(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $roleList = new RoleList([], '/user/roles');

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $roleList
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        return $result;
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRoleListElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'RoleList',
            ],
            $result,
            'Invalid <RoleList> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRoleListAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'RoleList',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.RoleList+xml',
                    'href' => '/user/roles',
                ],
            ],
            $result,
            'Invalid <RoleList> attributes.',
            false
        );
    }

    public function testRoleListVisitsChildren(): void
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $roleList = new RoleList(
            [
                new User\Role(),
                new User\Role(),
            ],
            '/user/roles'
        );

        $this->getVisitorMock()->expects(self::exactly(2))
            ->method('visitValueObject')
            ->with(self::isInstanceOf(Role::class));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $roleList
        );
    }

    protected function internalGetVisitor(): ValueObjectVisitor\RoleList
    {
        return new ValueObjectVisitor\RoleList();
    }
}
