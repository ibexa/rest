<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\RestContent;
use Ibexa\Rest\Server\Values\RestLocation;

class RestLocationRootNodeTest extends RestLocationTest
{
    /**
     * Test the Location visitor.
     */
    public function testVisit(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $location = new RestLocation(
            new Location(
                [
                    'id' => 1,
                    'priority' => 0,
                    'hidden' => false,
                    'invisible' => true,
                    'explicitlyHidden' => true,
                    'remoteId' => 'remote-id',
                    'parentLocationId' => 1,
                    'pathString' => '/1',
                    'depth' => 3,
                    'sortField' => Location::SORT_FIELD_PATH,
                    'sortOrder' => Location::SORT_ORDER_ASC,
                    'contentInfo' => new ContentInfo(
                        [
                            'id' => 42,
                            'contentTypeId' => 4,
                            'name' => 'A Node, long lost',
                        ]
                    ),
                ]
            ),
            // Dummy value for ChildCount
            0
        );

        $this->addRouteExpectation(
            'ibexa.rest.load_location',
            ['locationPath' => '1'],
            '/content/locations/1'
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_location_children',
            ['locationPath' => '1'],
            '/content/locations/1/children'
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_content',
            ['contentId' => $location->location->contentId],
            "/content/objects/{$location->location->contentId}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.list_location_url_aliases',
            ['locationPath' => '1'],
            '/content/objects/1/urlaliases'
        );

        $this->addRouteExpectation(
            'ibexa.rest.load_content',
            ['contentId' => $location->location->contentId],
            "/content/objects/{$location->location->contentId}"
        );

        $this->getVisitorMock()->expects(self::once())
            ->method('visitValueObject')
            ->with(self::isInstanceOf(RestContent::class));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $location
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        return $result;
    }

    /**
     * Test if result contains id value element.
     *
     * @depends testVisit
     */
    public function testResultContainsIdValueElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'id',
                'content' => '1',
            ],
            $result,
            'Invalid or non-existing <Location> id value element.',
            false
        );
    }

    /**
     * Test if result contains ParentLocation element.
     *
     * @depends testVisit
     */
    public function testResultContainsParentLocationElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'ParentLocation',
            ],
            $result,
            'Invalid <ParentLocation> element.',
            false
        );
    }

    /**
     * Test if result contains ParentLocation element attributes.
     *
     * @depends testVisit
     */
    public function testResultContainsParentLocationAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'ParentLocation',
                'attributes' => [],
            ],
            $result,
            'Invalid <ParentLocation> attributes.',
            false
        );
    }

    /**
     * Test if result contains Location element attributes.
     *
     * @depends testVisit
     */
    public function testResultContainsLocationAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Location',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.Location+xml',
                    'href' => '/content/locations/1',
                ],
            ],
            $result,
            'Invalid <Location> attributes.',
            false
        );
    }

    /**
     * Test if result contains Children element attributes.
     *
     * @depends testVisit
     */
    public function testResultContainsChildrenAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Children',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.LocationList+xml',
                    'href' => '/content/locations/1/children',
                ],
            ],
            $result,
            'Invalid <Children> attributes.',
            false
        );
    }

    /**
     * Test if result contains pathString value element.
     *
     * @depends testVisit
     */
    public function testResultContainsPathStringValueElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'pathString',
                'content' => '/1',
            ],
            $result,
            'Invalid or non-existing <Location> pathString value element.',
            false
        );
    }

    /**
     * Test if result contains Content element attributes.
     *
     * @depends testVisit
     */
    public function testResultContainsUrlAliasesTagAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'UrlAliases',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.UrlAliasRefList+xml',
                    'href' => '/content/objects/1/urlaliases',
                ],
            ],
            $result,
            'Invalid <UrlAliases> attributes.',
            false
        );
    }

    /**
     * Get the Location visitor.
     */
    protected function internalGetVisitor(): ValueObjectVisitor\RestLocation
    {
        return new ValueObjectVisitor\RestLocation();
    }
}
