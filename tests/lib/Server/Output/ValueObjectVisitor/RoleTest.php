<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Core\Repository\Values\User;
use Ibexa\Rest\Server\Output\ValueObjectVisitor\Role;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class RoleTest extends ValueObjectVisitorBaseTest
{
    public function testVisit(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $role = new User\Role(
            [
                'id' => 42,
                'identifier' => 'some-role',
                /* @todo uncomment when support for multilingual names and descriptions is added EZP-24776
                'mainLanguageCode' => 'eng-GB',
                'names' => array(
                    'eng-GB' => 'Role name EN',
                    'eng-US' => 'Role name EN US',
                ),
                'descriptions' => array(
                    'eng-GB' => 'Role description EN',
                    'eng-US' => 'Role description EN US',
                )
                */
            ]
        );

        $this->addRouteExpectation('ibexa.rest.load_role', ['roleId' => $role->id], "/user/roles/{$role->id}");
        $this->addRouteExpectation('ibexa.rest.load_policies', ['roleId' => $role->id], "/user/roles/{$role->id}/policies");

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $role
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        return $result;
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRoleElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Role',
                'children' => [
                    'count' => 2,
                ],
            ],
            $result,
            'Invalid <Role> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRoleAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Role',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.Role+xml',
                    'href' => '/user/roles/42',
                ],
            ],
            $result,
            'Invalid <Role> attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsIdentifierValueElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'identifier',
                'content' => 'some-role',
            ],
            $result,
            'Invalid or non-existing <Role> identifier value element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsMainLanguageCodeValueElement(string $result): void
    {
        self::markTestSkipped('@todo uncomment when support for multilingual names and descriptions is added EZP-24776');
        $this->assertXMLTag(
            [
                'tag' => 'mainLanguageCode',
                'content' => 'eng-GB',
            ],
            $result,
            'Invalid or non-existing <Role> mainLanguageCode value element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsNamesElement(string $result): void
    {
        self::markTestSkipped('@todo uncomment when support for multilingual names and descriptions is added EZP-24776');
        $this->assertXMLTag(
            [
                'tag' => 'names',
                'children' => [
                    'count' => 2,
                ],
            ],
            $result,
            'Invalid <names> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsDescriptionsElement(string $result): void
    {
        self::markTestSkipped('@todo uncomment when support for multilingual names and descriptions is added EZP-24776');
        $this->assertXMLTag(
            [
                'tag' => 'descriptions',
                'children' => [
                    'count' => 2,
                ],
            ],
            $result,
            'Invalid <descriptions> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsPoliciesElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Policies',
            ],
            $result,
            'Invalid <Policies> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsPoliciesAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Policies',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.PolicyList+xml',
                    'href' => '/user/roles/42/policies',
                ],
            ],
            $result,
            'Invalid <Policies> attributes.',
            false
        );
    }

    protected function internalGetVisitor(): Role
    {
        return new Role();
    }
}
