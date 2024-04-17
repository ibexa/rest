<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use DOMDocument;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Helper\TranslationHelper;
use Ibexa\Core\Repository\Values;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\RestContent;
use Ibexa\Rest\Server\Values\Version;

class RestContentTest extends BaseContentValueObjectVisitorTestCase
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

    public function testVisitWithoutEmbeddedVersion(): DOMDocument
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

        $dom = new DOMDocument();
        $dom->loadXml($result);

        return $dom;
    }

    protected function getBasicRestContent(): RestContent
    {
        return new RestContent(
            $this->getContentInfoStub(),
            new Values\Content\Location(
                [
                    'pathString' => '/1/2/23',
                ]
            ),
            null
        );
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testContentHrefCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/Content[@href="/content/objects/22"]');
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testContentIdCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/Content[@id="22"]');
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testContentMediaTypeWithoutVersionCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/Content[@media-type="application/vnd.ibexa.api.ContentInfo+xml"]');
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testContentRemoteIdCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/Content[@remoteId="abc123"]');
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testContentTypeHrefCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/Content/ContentType[@href="/content/types/26"]');
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testContentTypeMediaTypeCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/Content/ContentType[@media-type="application/vnd.ibexa.api.ContentType+xml"]');
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testNameCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/Content/Name[text()="Sindelfingen"]');
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testTranslatedNameCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/Content/TranslatedName[text()="Sindelfingen (Translated)"]');
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testCurrentVersionHrefCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/Content/CurrentVersion[@href="/content/objects/22/currentversion"]');
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testCurrentVersionMediaTypeCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/Content/CurrentVersion[@media-type="application/vnd.ibexa.api.Version+xml"]');
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testCurrentVersionNoCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/Content/currentVersionNo[text()="5"]');
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testIsHiddenCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/Content/isHidden[text()="true"]');
    }

    /**
     * @depends testVisitWithoutEmbeddedVersion
     */
    public function testStatusCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/Content/status[text()="PUBLISHED"]');
    }

    public function testVisitWithEmbeddedVersion(): DOMDocument
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

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $restContent
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        $dom = new DOMDocument();
        $dom->loadXml($result);

        return $dom;
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithEmbeddedVersion
     */
    public function testContentMediaTypeWithVersionCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/Content[@media-type="application/vnd.ibexa.api.Content+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithEmbeddedVersion
     */
    public function testEmbeddedCurrentVersionHrefCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/Content/CurrentVersion[@href="/content/objects/22/currentversion"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisitWithEmbeddedVersion
     */
    public function testEmbeddedCurrentVersionMediaTypeCorrect(DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/Content/CurrentVersion[@media-type="application/vnd.ibexa.api.Version+xml"]');
    }

    /**
     * Get the Content visitor.
     */
    protected function internalGetVisitor(): ValueObjectVisitor\RestContent
    {
        return new ValueObjectVisitor\RestContent(
            $this->translationHelper
        );
    }

    protected function getXPathFirstElementName(): string
    {
        return 'Content';
    }
}

class_alias(RestContentTest::class, 'EzSystems\EzPlatformRest\Tests\Server\Output\ValueObjectVisitor\RestContentTest');
