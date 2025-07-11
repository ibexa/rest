<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\User\Policy;
use Ibexa\Core\Repository\Values\User;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\PolicyList;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class PolicyListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the PolicyList visitor.
     */
    public function testVisit(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $policyList = new PolicyList([], '/user/roles/42/policies');

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $policyList
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        return $result;
    }

    /**
     * Test if result contains PolicyList element.
     *
     * @depends testVisit
     */
    public function testResultContainsPolicyListElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'PolicyList',
            ],
            $result,
            'Invalid <PolicyList> element.',
            false
        );
    }

    /**
     * Test if result contains PolicyList element attributes.
     *
     * @depends testVisit
     */
    public function testResultContainsPolicyListAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'PolicyList',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.PolicyList+xml',
                    'href' => '/user/roles/42/policies',
                ],
            ],
            $result,
            'Invalid <PolicyList> attributes.',
            false
        );
    }

    /**
     * Test if PolicyList visitor visits the children.
     */
    public function testPolicyListVisitsChildren(): void
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $policyList = new PolicyList(
            [
                new User\Policy(),
                new User\Policy(),
            ],
            42
        );

        $this->getVisitorMock()->expects(self::exactly(2))
            ->method('visitValueObject')
            ->with(self::isInstanceOf(Policy::class));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $policyList
        );
    }

    /**
     * Get the PolicyList visitor.
     */
    protected function internalGetVisitor(): ValueObjectVisitor\PolicyList
    {
        return new ValueObjectVisitor\PolicyList();
    }
}
