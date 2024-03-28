<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Helper\TranslationHelper;
use Ibexa\Core\Repository\Values;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\RestContent;
use Ibexa\Rest\Server\Values\Version;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class RestContentTest extends ValueObjectVisitorBaseTest
{
    /** @var \Ibexa\Core\Helper\TranslationHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $translationHelper;

    protected function setUp(): void
    {
        $this->translationHelper = $this->createMock(TranslationHelper::class);
        $this->translationHelper
            ->method('getTranslatedContentNameByContentInfo')
            ->willReturnCallback(static function (ContentInfo $content) {
                return $content->name . ' (Translated)';
            });
    }

    /**
     * @return \DOMDocument
     */
    public function testVisitWithoutEmbeddedVersion()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $restContent = $this->getBasicRestContent();

        $this->getVisitorMock()->expects($this->never())
            ->method('visitValueObject');

        $this->addRouteExpectation(
            'ibexa.rest.load_content',
            ['contentId' => $restContent->contentInfo->id],
            "/content/objects/{$restContent->contentInfo->id}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_content_type',
            ['contentTypeId' => $restContent->contentInfo->contentTypeId],
            "/content/types/{$restContent->contentInfo->contentTypeId}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_content_versions',
            ['contentId' => $restContent->contentInfo->id],
            "/content/objects/{$restContent->contentInfo->id}/versions"
        );
        $this->addRouteExpectation(
            'ibexa.rest.redirect_current_version',
            ['contentId' => $restContent->contentInfo->id],
            "/content/objects/{$restContent->contentInfo->id}/currentversion"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_section',
            ['sectionId' => $restContent->contentInfo->sectionId],
            "/content/sections/{$restContent->contentInfo->sectionId}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_location',
            ['locationPath' => $locationPath = trim($restContent->mainLocation->pathString, '/')],
            "/content/locations/{$locationPath}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_locations_for_content',
            ['contentId' => $restContent->contentInfo->id],
            "/content/objects/{$restContent->contentInfo->id}/locations"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_user',
            ['userId' => $restContent->contentInfo->ownerId],
            "/user/users/{$restContent->contentInfo->ownerId}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.get_object_states_for_content',
            ['contentId' => $restContent->contentInfo->id],
            "/content/objects/{$restContent->contentInfo->id}/objectstates"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $restContent
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        $dom = new \DOMDocument();
        $dom->loadXml($result);

        return $dom;
    }

    protected function getBasicRestContent()
    {
        return new RestContent(
            new ContentInfo(
                [
                    'id' => 22,
                    'name' => 'Sindelfingen',
                    'sectionId' => 23,
                    'currentVersionNo' => 5,
                    'published' => true,
                    'ownerId' => 24,
                    'modificationDate' => new \DateTime('2012-09-05 15:27 Europe/Berlin'),
                    'publishedDate' => null,
                    'alwaysAvailable' => true,
                    'status' => ContentInfo::STATUS_PUBLISHED,
                    'remoteId' => 'abc123',
                    'mainLanguageCode' => 'eng-US',
                    'mainLocationId' => 'location23',
                    'contentTypeId' => 'contentType23',
                    'isHidden' => true,
                ]
            ),
            new Values\Content\Location(
                [
                    'pathString' => '/1/2/23',
                ]
            ),
            null
        );
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testContentHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/Content[@href="/content/objects/22"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testContentIdCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/Content[@id="22"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testContentMediaTypeWithoutVersionCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/Content[@media-type="application/vnd.ibexa.api.ContentInfo+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testContentRemoteIdCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/Content[@remoteId="abc123"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testContentTypeHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/Content/ContentType[@href="/content/types/contentType23"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testContentTypeMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/Content/ContentType[@media-type="application/vnd.ibexa.api.ContentType+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testNameCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/Content/Name[text()="Sindelfingen"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testTranslatedNameCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/Content/TranslatedName[text()="Sindelfingen (Translated)"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testVersionsHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/Content/Versions[@href="/content/objects/22/versions"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testVersionsMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/Content/Versions[@media-type="application/vnd.ibexa.api.VersionList+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testCurrentVersionHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/Content/CurrentVersion[@href="/content/objects/22/currentversion"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testCurrentVersionMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/Content/CurrentVersion[@media-type="application/vnd.ibexa.api.Version+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testSectionHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/Content/Section[@href="/content/sections/23"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testSectionMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/Content/Section[@media-type="application/vnd.ibexa.api.Section+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testMainLocationHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/Content/MainLocation[@href="/content/locations/1/2/23"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testMainLocationMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/Content/MainLocation[@media-type="application/vnd.ibexa.api.Location+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testLocationsHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/Content/Locations[@href="/content/objects/22/locations"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testLocationsMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/Content/Locations[@media-type="application/vnd.ibexa.api.LocationList+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testOwnerHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/Content/Owner[@href="/user/users/24"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testOwnerMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/Content/Owner[@media-type="application/vnd.ibexa.api.User+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testLastModificationDateCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/Content/lastModificationDate[text()="2012-09-05T15:27:00+02:00"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testMainLanguageCodeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/Content/mainLanguageCode[text()="eng-US"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testCurrentVersionNoCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/Content/currentVersionNo[text()="5"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testAlwaysAvailableCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/Content/alwaysAvailable[text()="true"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testIsHiddenCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/Content/isHidden[text()="true"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testStatusCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/Content/status[text()="PUBLISHED"]');
    }

    /**
     * @return \DOMDocument
     */
    public function testVisitWithEmbeddedVersion()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $restContent = $this->getBasicRestContent();
        $restContent->currentVersion = new Values\Content\Content(
            [
                'versionInfo' => new Values\Content\VersionInfo(['versionNo' => 5]),
                'internalFields' => [],
            ]
        );
        $restContent->relations = [];
        $restContent->contentType = $this->getMockForAbstractClass(
            ContentType::class
        );

        $this->getVisitorMock()->expects($this->once())
            ->method('visitValueObject')
            ->with($this->isInstanceOf(Version::class));

        $this->addRouteExpectation(
            'ibexa.rest.load_content',
            ['contentId' => $restContent->contentInfo->id],
            "/content/objects/{$restContent->contentInfo->id}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_content_type',
            ['contentTypeId' => $restContent->contentInfo->contentTypeId],
            "/content/types/{$restContent->contentInfo->contentTypeId}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_content_versions',
            ['contentId' => $restContent->contentInfo->id],
            "/content/objects/{$restContent->contentInfo->id}/versions"
        );
        $this->addRouteExpectation(
            'ibexa.rest.redirect_current_version',
            ['contentId' => $restContent->contentInfo->id],
            "/content/objects/{$restContent->contentInfo->id}/currentversion"
        );

        $this->addRouteExpectation(
            'ibexa.rest.load_section',
            ['sectionId' => $restContent->contentInfo->sectionId],
            "/content/sections/{$restContent->contentInfo->sectionId}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_location',
            ['locationPath' => $locationPath = trim($restContent->mainLocation->pathString, '/')],
            "/content/locations/{$locationPath}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_locations_for_content',
            ['contentId' => $restContent->contentInfo->id],
            "/content/objects/{$restContent->contentInfo->id}/locations"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_user',
            ['userId' => $restContent->contentInfo->ownerId],
            "/user/users/{$restContent->contentInfo->ownerId}"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $restContent
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        $dom = new \DOMDocument();
        $dom->loadXml($result);

        return $dom;
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithEmbeddedVersion
     */
    public function testContentMediaTypeWithVersionCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/Content[@media-type="application/vnd.ibexa.api.Content+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithEmbeddedVersion
     */
    public function testEmbeddedCurrentVersionHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/Content/CurrentVersion[@href="/content/objects/22/currentversion"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithEmbeddedVersion
     */
    public function testEmbeddedCurrentVersionMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/Content/CurrentVersion[@media-type="application/vnd.ibexa.api.Version+xml"]');
    }

    /**
     * Get the Content visitor.
     *
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\RestContent
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\RestContent(
            $this->translationHelper
        );
    }
}

class_alias(RestContentTest::class, 'EzSystems\EzPlatformRest\Tests\Server\Output\ValueObjectVisitor\RestContentTest');
