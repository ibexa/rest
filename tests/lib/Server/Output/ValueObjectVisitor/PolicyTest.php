<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\User\Limitation\ContentTypeLimitation;
use Ibexa\Core\Repository\Values\User;
use Ibexa\Rest\Server\Output\ValueObjectVisitor\Policy;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class PolicyTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the Policy visitor.
     */
    public function testVisit(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $contentTypeLimitation = new ContentTypeLimitation();
        $contentTypeLimitation->limitationValues = [1, 2, 3];

        $policy = new User\Policy(
            [
                'id' => 42,
                'roleId' => '84',
                'module' => 'content',
                'function' => 'delete',
                'limitations' => [
                    'Class' => $contentTypeLimitation,
                ],
            ]
        );

        $this->addRouteExpectation(
            'ibexa.rest.load_policy',
            ['roleId' => $policy->roleId, 'policyId' => $policy->id],
            "/user/roles/{$policy->roleId}/policies/{$policy->id}"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $policy
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        return $result;
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsPolicyElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Policy',
                'children' => [
                    'less_than' => 5,
                    'greater_than' => 2,
                ],
            ],
            $result,
            'Invalid <Policy> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsPolicyAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Policy',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.Policy+xml',
                    'href' => '/user/roles/84/policies/42',
                ],
            ],
            $result,
            'Invalid <Policy> attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsIdValueElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'id',
                'content' => '42',
            ],
            $result,
            'Invalid or non-existing <Policy> id value element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsModuleValueElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'module',
                'content' => 'content',
            ],
            $result,
            'Invalid or non-existing <Policy> module value element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsFunctionValueElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'function',
                'content' => 'delete',
            ],
            $result,
            'Invalid or non-existing <Policy> function value element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsLimitationsElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'limitations',
            ],
            $result,
            'Invalid <limitations> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsLimitationsAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'limitations',
            ],
            $result,
            'Invalid <limitations> attributes.',
            false
        );
    }

    /**
     * Get the Policy visitor.
     *
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\Policy
     */
    protected function internalGetVisitor(): Policy
    {
        return new Policy();
    }
}
