<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use DateTime;
use DOMDocument;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

abstract class BaseContentValueObjectVisitorTestCase extends ValueObjectVisitorBaseTest
{
    abstract public function testVisitWithoutEmbeddedVersion(): DOMDocument;

    abstract protected function getXPathFirstElementName(): string;

    protected function getContentInfoStub(): ContentInfo
    {
        return new ContentInfo(
            [
                'id' => 22,
                'name' => 'Sindelfingen',
                'sectionId' => 23,
                'currentVersionNo' => 5,
                'published' => true,
                'ownerId' => 24,
                'modificationDate' => new DateTime('2012-09-05 15:27 Europe/Berlin'),
                'publishedDate' => new DateTime('2012-09-05 15:27 Europe/Berlin'),
                'alwaysAvailable' => true,
                'status' => ContentInfo::STATUS_PUBLISHED,
                'remoteId' => 'abc123',
                'mainLanguageCode' => 'eng-US',
                'mainLocationId' => 25,
                'contentTypeId' => 26,
                'contentType' => new ContentType(['id' => 26]),
                'isHidden' => true,
            ]
        );
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testNameCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, sprintf('/%s/name[text()="Sindelfingen"]', $this->getXPathFirstElementName()));
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testVersionsHrefCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, sprintf('/%s/Versions[@href="/content/objects/22/versions"]', $this->getXPathFirstElementName()));
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testVersionsMediaTypeCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, sprintf('/%s/Versions[@media-type="application/vnd.ibexa.api.VersionList+xml"]', $this->getXPathFirstElementName()));
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testSectionHrefCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, sprintf('/%s/Section[@href="/content/sections/23"]', $this->getXPathFirstElementName()));
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testSectionMediaTypeCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, sprintf('/%s/Section[@media-type="application/vnd.ibexa.api.Section+xml"]', $this->getXPathFirstElementName()));
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testMainLocationHrefCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, sprintf('/%s/MainLocation[@href="/content/locations/1/2/23"]', $this->getXPathFirstElementName()));
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testMainLocationMediaTypeCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, sprintf('/%s/MainLocation[@media-type="application/vnd.ibexa.api.Location+xml"]', $this->getXPathFirstElementName()));
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testLocationsHrefCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, sprintf('/%s/Locations[@href="/content/objects/22/locations"]', $this->getXPathFirstElementName()));
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testLocationsMediaTypeCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, sprintf('/%s/Locations[@media-type="application/vnd.ibexa.api.LocationList+xml"]', $this->getXPathFirstElementName()));
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testOwnerHrefCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, sprintf('/%s/Owner[@href="/user/users/24"]', $this->getXPathFirstElementName()));
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testOwnerMediaTypeCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, sprintf('/%s/Owner[@media-type="application/vnd.ibexa.api.User+xml"]', $this->getXPathFirstElementName()));
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testLastModificationDateCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, sprintf('/%s/lastModificationDate[text()="2012-09-05T15:27:00+02:00"]', $this->getXPathFirstElementName()));
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testMainLanguageCodeCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, sprintf('/%s/mainLanguageCode[text()="eng-US"]', $this->getXPathFirstElementName()));
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testAlwaysAvailableCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, sprintf('/%s/alwaysAvailable[text()="true"]', $this->getXPathFirstElementName()));
    }

    protected function addContentRouteExpectations(ContentInfo $contentInfo, Location $location): void
    {
        $contentId = $contentInfo->getId();
        $contentTypeId = $contentInfo->getContentType()->id;
        $sectionId = $contentInfo->getSectionId();

        $this->addRouteExpectation(
            'ibexa.rest.load_content_type',
            ['contentTypeId' => $contentTypeId],
            "/content/types/$contentTypeId"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_content_versions',
            ['contentId' => $contentId],
            "/content/objects/$contentId/versions"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_section',
            ['sectionId' => $sectionId],
            "/content/sections/$sectionId"
        );
        $locationPath = trim($location->getPathString(), '/');
        $this->addRouteExpectation(
            'ibexa.rest.load_location',
            ['locationPath' => $locationPath],
            "/content/locations/$locationPath"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_locations_for_content',
            ['contentId' => $contentId],
            "/content/objects/$contentId/locations"
        );
    }
}
