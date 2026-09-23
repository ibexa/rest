<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Core\Repository\Values\ObjectState;
use Ibexa\Rest\Server\Output\ValueObjectVisitor\ObjectStateGroup;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTestCase;
use PHPUnit\Framework\Attributes\Depends;

class ObjectStateGroupTest extends ValueObjectVisitorBaseTestCase
{
    public function testVisit(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $objectStateGroup = new ObjectState\ObjectStateGroup(
            [
                'id' => 42,
                'identifier' => 'test-group',
                'mainLanguageCode' => 'eng-GB',
                'languageCodes' => ['eng-GB', 'eng-US'],
                'names' => [
                    'eng-GB' => 'Group name EN',
                    'eng-US' => 'Group name EN US',
                ],
                'descriptions' => [
                    'eng-GB' => 'Group description EN',
                    'eng-US' => 'Group description EN US',
                ],
            ]
        );

        $this->addRouteExpectation(
            'ibexa.rest.load_object_state_group',
            ['objectStateGroupId' => $objectStateGroup->id],
            "/content/objectstategroups/$objectStateGroup->id"
        );

        $this->addRouteExpectation(
            'ibexa.rest.load_object_states',
            ['objectStateGroupId' => $objectStateGroup->id],
            "/content/objectstategroups/$objectStateGroup->id/objectstates"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $objectStateGroup
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        return $result;
    }

    #[Depends('testVisit')]
    public function testResultContainsObjectStateGroupElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'ObjectStateGroup',
                'children' => [
                    'count' => 7,
                ],
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
                    'href' => '/content/objectstategroups/42',
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
            'Invalid or non-existing <ObjectStateGroup> id value element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsIdentifierValueElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'identifier',
                'content' => 'test-group',
            ],
            $result,
            'Invalid or non-existing <ObjectStateGroup> identifier value element.'
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
            'Invalid or non-existing <ObjectStateGroup> defaultLanguageCode value element.'
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
            'Invalid or non-existing <ObjectStateGroup> languageCodes value element.'
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

    protected function internalGetVisitor(): ObjectStateGroup
    {
        return new ObjectStateGroup();
    }
}
