<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\LocationList;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTestCase;
use PHPUnit\Framework\Attributes\Depends;

class LocationListTest extends ValueObjectVisitorBaseTestCase
{
    /**
     * Test the LocationList visitor.
     */
    public function testVisit(): string
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

    #[Depends('testVisit')]
    public function testResultContainsLocationListElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'LocationList',
            ],
            $result,
            'Invalid <LocationList> element.'
        );
    }

    /**
     * Test if result contains LocationList element attributes.
     *
     * @param string $result
     */
    #[Depends('testVisit')]
    public function testResultContainsLocationListAttributes($result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'LocationList',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.LocationList+xml',
                    'href' => '/content/objects/42/locations',
                ],
            ],
            $result,
            'Invalid <LocationList> attributes.'
        );
    }

    /**
     * Get the LocationList visitor.
     */
    protected function internalGetVisitor(): ValueObjectVisitor\LocationList
    {
        return new ValueObjectVisitor\LocationList();
    }
}
