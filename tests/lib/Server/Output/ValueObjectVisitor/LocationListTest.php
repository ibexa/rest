<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\LocationList;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class LocationListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the LocationList visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        // @todo coverage test with a list of values
        $locationList = new LocationList([], '/content/objects/42/locations');

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $locationList
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        return $result;
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsLocationListElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'LocationList',
            ],
            $result,
            'Invalid <LocationList> element.',
            false
        );
    }

    /**
     * Test if result contains LocationList element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsLocationListAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'LocationList',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.LocationList+xml',
                    'href' => '/content/objects/42/locations',
                ],
            ],
            $result,
            'Invalid <LocationList> attributes.',
            false
        );
    }

    /**
     * Get the LocationList visitor.
     *
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\LocationList
     */
    protected function internalGetVisitor(): ValueObjectVisitor\LocationList
    {
        return new ValueObjectVisitor\LocationList();
    }
}
