<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Core\Repository\Values\ObjectState;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class ObjectStateGroupTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the ObjectStateGroup visitor.
     *
     * @return string
     */
    public function testVisit()
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

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains ObjectStateGroup element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsObjectStateGroupElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'ObjectStateGroup',
                'children' => [
                    'count' => 7,
                ],
            ],
            $result,
            'Invalid <ObjectStateGroup> element.',
            false
        );
    }

    /**
     * Test if result contains ObjectStateGroup element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsObjectStateGroupAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'ObjectStateGroup',
                'attributes' => [
                    'media-type' => 'application/vnd.ez.api.ObjectStateGroup+xml',
                    'href' => '/content/objectstategroups/42',
                ],
            ],
            $result,
            'Invalid <ObjectStateGroup> attributes.',
            false
        );
    }

    /**
     * Test if result contains id value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsIdValueElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'id',
                'content' => '42',
            ],
            $result,
            'Invalid or non-existing <ObjectStateGroup> id value element.',
            false
        );
    }

    /**
     * Test if result contains identifier value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsIdentifierValueElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'identifier',
                'content' => 'test-group',
            ],
            $result,
            'Invalid or non-existing <ObjectStateGroup> identifier value element.',
            false
        );
    }

    /**
     * Test if result contains defaultLanguageCode value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsDefaultLanguageCodeValueElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'defaultLanguageCode',
                'content' => 'eng-GB',
            ],
            $result,
            'Invalid or non-existing <ObjectStateGroup> defaultLanguageCode value element.',
            false
        );
    }

    /**
     * Test if result contains languageCodes value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsLanguageCodesValueElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'languageCodes',
                'content' => 'eng-GB,eng-US',
            ],
            $result,
            'Invalid or non-existing <ObjectStateGroup> languageCodes value element.',
            false
        );
    }

    /**
     * Test if result contains names element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsNamesElement($result)
    {
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
     * Test if result contains descriptions element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsDescriptionsElement($result)
    {
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
     * Get the ObjectStateGroup visitor.
     *
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\ObjectStateGroup
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\ObjectStateGroup();
    }
}

class_alias(ObjectStateGroupTest::class, 'EzSystems\EzPlatformRest\Tests\Server\Output\ValueObjectVisitor\ObjectStateGroupTest');
