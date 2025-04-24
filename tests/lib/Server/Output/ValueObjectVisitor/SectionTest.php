<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content;
use Ibexa\Rest\Server\Output\ValueObjectVisitor\Section;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class SectionTest extends ValueObjectVisitorBaseTest
{
    public function testVisit(): string
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

        self::assertNotEmpty($result);

        return $result;
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsSectionElement(string $result): void
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
     * @depends testVisit
     */
    public function testResultContainsSectionAttributes(string $result): void
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
     * @depends testVisit
     */
    public function testResultContainsSectionIdValueElement(string $result): void
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
     * @depends testVisit
     */
    public function testResultContainsIdentifierValueElement(string $result): void
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
     * @depends testVisit
     */
    public function testResultContainsNameValueElement(string $result): void
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

    protected function internalGetVisitor(): Section
    {
        return new Section();
    }
}
