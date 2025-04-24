<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\SectionList;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class SectionListTest extends ValueObjectVisitorBaseTest
{
    public function testVisit(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $sectionList = new SectionList([], '/content/sections');

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $sectionList
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        return $result;
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsSectionListElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'SectionList',
            ],
            $result,
            'Invalid <SectionList> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsSectionListAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'SectionList',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.SectionList+xml',
                    'href' => '/content/sections',
                ],
            ],
            $result,
            'Invalid <SectionList> attributes.',
            false
        );
    }

    public function testSectionListVisitsChildren(): void
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $sectionList = new SectionList(
            [
                new Content\Section(),
                new Content\Section(),
            ],
            '/content/sections'
        );

        $this->getVisitorMock()->expects(self::exactly(2))
            ->method('visitValueObject')
            ->with(self::isInstanceOf(Content\Section::class));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $sectionList
        );
    }

    protected function internalGetVisitor(): ValueObjectVisitor\SectionList
    {
        return new ValueObjectVisitor\SectionList();
    }
}
