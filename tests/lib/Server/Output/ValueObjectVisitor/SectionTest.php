<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class SectionTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the Section visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $section = new Content\Section(
            [
                'id' => 23,
                'identifier' => 'some-section',
                'name' => 'Some Section',
            ]
        );

        $this->addRouteExpectation(
            'ibexa.rest.load_section',
            ['sectionId' => $section->id],
            "/content/sections/{$section->id}"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $section
        );

        $result = $generator->endDocument(null);

        self::assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains Section element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsSectionElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'Section',
                'children' => [
                    'less_than' => 4,
                    'greater_than' => 2,
                ],
            ],
            $result,
            'Invalid <Section> element.',
            false
        );
    }

    /**
     * Test if result contains Section element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsSectionAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'Section',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.Section+xml',
                    'href' => '/content/sections/23',
                ],
            ],
            $result,
            'Invalid <Section> attributes.',
            false
        );
    }

    /**
     * Test if result contains sectionId value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsSectionIdValueElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'sectionId',
                'content' => '23',
            ],
            $result,
            'Invalid or non-existing <Section> sectionId value element.',
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
                'content' => 'some-section',
            ],
            $result,
            'Invalid or non-existing <Section> identifier value element.',
            false
        );
    }

    /**
     * Test if result contains name value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsNameValueElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'name',
                'content' => 'Some Section',
            ],
            $result,
            'Invalid or non-existing <Section> name value element.',
            false
        );
    }

    /**
     * Get the Section visitor.
     *
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\Section
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\Section();
    }
}
