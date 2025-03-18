<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Core\Repository\Values\ObjectState\ObjectState;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\ObjectStateList;
use Ibexa\Rest\Values\RestObjectState;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class ObjectStateListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the ObjectStateList visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        // @todo coverage add actual object states + visitor mock for RestObjectState
        $stateList = new ObjectStateList([], 42);

        $this->addRouteExpectation(
            'ibexa.rest.load_object_states',
            ['objectStateGroupId' => $stateList->groupId],
            "/content/objectstategroups/{$stateList->groupId}/objectstates"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $stateList
        );

        $result = $generator->endDocument(null);

        self::assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains ObjectStateList element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsObjectStateListElement($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'ObjectStateList',
            ],
            $result,
            'Invalid <ObjectStateList> element.',
            false
        );
    }

    /**
     * Test if result contains ObjectStateList element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsObjectStateListAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'ObjectStateList',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.ObjectStateList+xml',
                    'href' => '/content/objectstategroups/42/objectstates',
                ],
            ],
            $result,
            'Invalid <ObjectStateList> attributes.',
            false
        );
    }

    /**
     * Test if ObjectStateList visitor visits the children.
     */
    public function testObjectStateListVisitsChildren(): void
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $objectStateList = new ObjectStateList(
            [
                new ObjectState(),
                new ObjectState(),
            ],
            42
        );

        $this->getVisitorMock()->expects(self::exactly(2))
            ->method('visitValueObject')
            ->with(self::isInstanceOf(RestObjectState::class));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $objectStateList
        );
    }

    /**
     * Get the ObjectStateList visitor.
     *
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\ObjectStateList
     */
    protected function internalGetVisitor(): ValueObjectVisitor\ObjectStateList
    {
        return new ValueObjectVisitor\ObjectStateList();
    }
}
