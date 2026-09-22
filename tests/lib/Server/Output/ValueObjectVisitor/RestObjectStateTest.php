<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Core\Repository\Values\ObjectState\ObjectState;
use Ibexa\Rest\Server\Output\ValueObjectVisitor\RestObjectState;
use Ibexa\Rest\Values;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTestCase;
use PHPUnit\Framework\Attributes\Depends;

class RestObjectStateTest extends ValueObjectVisitorBaseTestCase
{
    public function testVisit(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $objectState = new Values\RestObjectState(
            new ObjectState(
                [
                    'id' => 42,
                    'identifier' => 'test-state',
                    'priority' => '0',
                    'mainLanguageCode' => 'eng-GB',
                    'languageCodes' => ['eng-GB', 'eng-US'],
                    'names' => [
                        'eng-GB' => 'State name EN',
                        'eng-US' => 'State name EN US',
                    ],
                    'descriptions' => [
                        'eng-GB' => 'State description EN',
                        'eng-US' => 'State description EN US',
                    ],
                ]
            ),
            21
        );

        $this->addRouteExpectation(
            'ibexa.rest.load_object_state',
            ['objectStateGroupId' => $objectState->groupId, 'objectStateId' => $objectState->objectState->id],
            "/content/objectstategroups/{$objectState->groupId}/objectstates/{$objectState->objectState->id}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_object_state_group',
            ['objectStateGroupId' => $objectState->groupId],
            "/content/objectstategroups/{$objectState->groupId}"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $objectState
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        return $result;
    }

    #[Depends('testVisit')]
    public function testResultContainsObjectStateElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'ObjectState',
                'children' => [
                    'count' => 8,
                ],
            ],
            $result,
            'Invalid <ObjectState> element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsObjectStateAttributes(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'ObjectState',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.ObjectState+xml',
                    'href' => '/content/objectstategroups/21/objectstates/42',
                ],
            ],
            $result,
            'Invalid <ObjectState> attributes.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsObjectStateGroupElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'ObjectStateGroup',
            ],
            $result,
            'Invalid <ObjectStateGroup> element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsObjectStateGroupAttributes(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'ObjectStateGroup',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.ObjectStateGroup+xml',
                    'href' => '/content/objectstategroups/21',
                ],
            ],
            $result,
            'Invalid <ObjectStateGroup> attributes.'
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
            'Invalid or non-existing <ObjectState> id value element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsIdentifierValueElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'identifier',
                'content' => 'test-state',
            ],
            $result,
            'Invalid or non-existing <ObjectState> identifier value element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsPriorityValueElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'priority',
                'content' => '0',
            ],
            $result,
            'Invalid or non-existing <ObjectState> priority value element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsDefaultLanguageCodeValueElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'defaultLanguageCode',
                'content' => 'eng-GB',
            ],
            $result,
            'Invalid or non-existing <ObjectState> defaultLanguageCode value element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsLanguageCodesValueElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'languageCodes',
                'content' => 'eng-GB,eng-US',
            ],
            $result,
            'Invalid or non-existing <ObjectState> languageCodes value element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsNamesElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'names',
                'children' => [
                    'count' => 2,
                ],
            ],
            $result,
            'Invalid <names> element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsDescriptionsElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'descriptions',
                'children' => [
                    'count' => 2,
                ],
            ],
            $result,
            'Invalid <descriptions> element.'
        );
    }

    protected function internalGetVisitor(): RestObjectState
    {
        return new RestObjectState();
    }
}
