<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\User\Limitation\ContentTypeLimitation;
use Ibexa\Core\Repository\Values\User;
use Ibexa\Rest\Server\Output\ValueObjectVisitor\Policy;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTestCase;
use PHPUnit\Framework\Attributes\Depends;

class PolicyTest extends ValueObjectVisitorBaseTestCase
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

    #[Depends('testVisit')]
    public function testResultContainsPolicyElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'Policy',
                'children' => [
                    'less_than' => 5,
                    'greater_than' => 2,
                ],
            ],
            $result,
            'Invalid <Policy> element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsPolicyAttributes(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'Policy',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.Policy+xml',
                    'href' => '/user/roles/84/policies/42',
                ],
            ],
            $result,
            'Invalid <Policy> attributes.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsIdValueElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'id',
                'content' => '42',
            ],
            $result,
            'Invalid or non-existing <Policy> id value element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsModuleValueElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'module',
                'content' => 'content',
            ],
            $result,
            'Invalid or non-existing <Policy> module value element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsFunctionValueElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'function',
                'content' => 'delete',
            ],
            $result,
            'Invalid or non-existing <Policy> function value element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsLimitationsElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'limitations',
            ],
            $result,
            'Invalid <limitations> element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsLimitationsAttributes(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'limitations',
            ],
            $result,
            'Invalid <limitations> attributes.'
        );
    }

    /**
     * Get the Policy visitor.
     */
    protected function internalGetVisitor(): Policy
    {
        return new Policy();
    }
}
