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
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class RestLocationTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the Location visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $location = new RestLocation(
            new Location(
                [
                    'id' => 42,
                    'priority' => 0,
                    'hidden' => false,
                    'invisible' => true,
                    'explicitlyHidden' => true,
                    'remoteId' => 'remote-id',
                    'parentLocationId' => 21,
                    'pathString' => '/1/2/21/42/',
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
            ['locationPath' => '1/2/21/42'],
            '/content/locations/1/2/21/42'
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_location',
            ['locationPath' => '1/2/21'],
            '/content/locations/1/2/21'
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_location_children',
            ['locationPath' => '1/2/21/42'],
            '/content/locations/1/2/21/42/children'
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_content',
            ['contentId' => $location->location->contentId],
            "/content/objects/{$location->location->contentId}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.list_location_url_aliases',
            ['locationPath' => '1/2/21/42'],
            '/content/objects/1/2/21/42/urlaliases'
        );

        // Expected twice, second one here for ContentInfo
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

        self::assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains Location element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsLocationElement($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Location',
            ],
            $result,
            'Invalid <Location> element.',
            false
        );
    }

    /**
     * Test if result contains Location element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsLocationAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Location',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.Location+xml',
                    'href' => '/content/locations/1/2/21/42',
                ],
            ],
            $result,
            'Invalid <Location> attributes.',
            false
        );
    }

    /**
     * Test if result contains ContentInfo element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentInfoElement($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'ContentInfo',
            ],
            $result,
            'Invalid <ContentInfo> element.',
            false
        );
    }

    /**
     * Test if result contains Location element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentInfoAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'ContentInfo',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.ContentInfo+xml',
                    'href' => '/content/objects/42',
                ],
            ],
            $result,
            'Invalid <ContentInfo> attributes.',
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
    public function testResultContainsIdValueElement($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'id',
                'content' => '42',
            ],
            $result,
            'Invalid or non-existing <Location> id value element.',
            false
        );
    }

    /**
     * Test if result contains priority value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsPriorityValueElement($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'priority',
                'content' => '0',
            ],
            $result,
            'Invalid or non-existing <Location> priority value element.',
            false
        );
    }

    /**
     * Test if result contains hidden value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsHiddenValueElement($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'hidden',
                'content' => 'false',
            ],
            $result,
            'Invalid or non-existing <Location> hidden value element.',
            false
        );
    }

    /**
     * Test if result contains invisible value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsInvisibleValueElement($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'invisible',
                'content' => 'true',
            ],
            $result,
            'Invalid or non-existing <Location> invisible value element.',
            false
        );
    }

    /**
     * Test if result contains explicitlyHidden value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsExplicitlyHiddenValueElement($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'explicitlyHidden',
                'content' => 'true',
            ],
            $result,
            'Invalid or non-existing <Location> explicitlyHidden value element.'
        );
    }

    /**
     * Test if result contains remoteId value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRemoteIdValueElement($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'remoteId',
                'content' => 'remote-id',
            ],
            $result,
            'Invalid or non-existing <Location> remoteId value element.',
            false
        );
    }

    /**
     * Test if result contains Children element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsChildrenElement($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Children',
            ],
            $result,
            'Invalid <Children> element.',
            false
        );
    }

    /**
     * Test if result contains Children element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsChildrenAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Children',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.LocationList+xml',
                    'href' => '/content/locations/1/2/21/42/children',
                ],
            ],
            $result,
            'Invalid <Children> attributes.',
            false
        );
    }

    /**
     * Test if result contains ParentLocation element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsParentLocationElement($result): void
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
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsParentLocationAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'ParentLocation',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.Location+xml',
                    'href' => '/content/locations/1/2/21',
                ],
            ],
            $result,
            'Invalid <ParentLocation> attributes.',
            false
        );
    }

    /**
     * Test if result contains Content element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentElement($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Content',
            ],
            $result,
            'Invalid <Content> element.',
            false
        );
    }

    /**
     * Test if result contains Content element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Content',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.Content+xml',
                    'href' => '/content/objects/42',
                ],
            ],
            $result,
            'Invalid <Content> attributes.',
            false
        );
    }

    /**
     * Test if result contains pathString value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsPathStringValueElement($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'pathString',
                'content' => '/1/2/21/42/',
            ],
            $result,
            'Invalid or non-existing <Location> pathString value element.',
            false
        );
    }

    /**
     * Test if result contains depth value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsDepthValueElement($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'depth',
                'content' => '3',
            ],
            $result,
            'Invalid or non-existing <Location> depth value element.',
            false
        );
    }

    /**
     * Test if result contains sortField value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsSortFieldValueElement($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'sortField',
                'content' => 'PATH',
            ],
            $result,
            'Invalid or non-existing <Location> sortField value element.',
            false
        );
    }

    /**
     * Test if result contains sortOrder value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsSortOrderValueElement($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'sortOrder',
                'content' => 'ASC',
            ],
            $result,
            'Invalid or non-existing <Location> sortOrder value element.',
            false
        );
    }

    /**
     * Test if result contains childCount value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsChildCountValueElement($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'childCount',
                'content' => '0',
            ],
            $result,
            'Invalid or non-existing <Location> childCount value element.',
            false
        );
    }

    /**
     * Test if result contains Content element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsUrlAliasesTag($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'UrlAliases',
            ],
            $result,
            'Invalid <UrlAliases> element.',
            false
        );
    }

    /**
     * Test if result contains Content element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsUrlAliasesTagAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'UrlAliases',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.UrlAliasRefList+xml',
                    'href' => '/content/objects/1/2/21/42/urlaliases',
                ],
            ],
            $result,
            'Invalid <UrlAliases> attributes.',
            false
        );
    }

    /**
     * Get the Location visitor.
     *
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\RestLocation
     */
    protected function internalGetVisitor(): ValueObjectVisitor\RestLocation
    {
        return new ValueObjectVisitor\RestLocation();
    }
}
