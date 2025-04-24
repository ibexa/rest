<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Core\Repository\Values\ObjectState\ObjectStateGroup;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\ObjectStateGroupList;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class ObjectStateGroupListTest extends ValueObjectVisitorBaseTest
{
    public function testVisit(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $groupList = new ObjectStateGroupList([]);

        $this->addRouteExpectation('ibexa.rest.load_object_state_groups', [], '/content/objectstategroups');

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $groupList
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        return $result;
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsObjectStateGroupListElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'ObjectStateGroupList',
            ],
            $result,
            'Invalid <ObjectStateGroupList> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsObjectStateGroupListAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'ObjectStateGroupList',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.ObjectStateGroupList+xml',
                    'href' => '/content/objectstategroups',
                ],
            ],
            $result,
            'Invalid <ObjectStateGroupList> attributes.',
            false
        );
    }

    /**
     * Test if ObjectStateGroupList visitor visits the children.
     */
    public function testObjectStateGroupListVisitsChildren(): void
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $groupList = new ObjectStateGroupList(
            [
                new ObjectStateGroup(),
                new ObjectStateGroup(),
            ]
        );

        $this->getVisitorMock()->expects(self::exactly(2))
            ->method('visitValueObject')
            ->with(self::isInstanceOf(\Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup::class));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $groupList
        );
    }

    protected function internalGetVisitor(): ValueObjectVisitor\ObjectStateGroupList
    {
        return new ValueObjectVisitor\ObjectStateGroupList();
    }
}
