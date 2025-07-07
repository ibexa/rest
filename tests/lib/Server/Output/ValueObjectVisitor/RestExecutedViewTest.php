<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use DOMDocument;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchHit;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Ibexa\Core\Repository\Values\Content;
use Ibexa\Core\Repository\Values\Content as ApiValues;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\RestExecutedView;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;
use PHPUnit\Framework\MockObject\MockObject;

class RestExecutedViewTest extends ValueObjectVisitorBaseTest
{
    private const int EXAMPLE_LOCATION_ID = 54;

    public function testVisit(): DOMDocument
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $view = new RestExecutedView(
            [
                'identifier' => 'test_view',
                'searchResults' => new SearchResult([
                    'totalCount' => null,
                    'searchHits' => [
                        $this->buildContentSearchHit(),
                        $this->buildLocationSearchHit(),
                    ],
                ]),
            ]
        );

        $this->addRouteExpectation(
            'ibexa.rest.views.load',
            ['viewId' => $view->identifier],
            "/content/views/{$view->identifier}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.views.load.results',
            ['viewId' => $view->identifier],
            "/content/views/{$view->identifier}/results"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $view
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        $dom = new DOMDocument();
        $dom->loadXml($result);

        return $dom;
    }

    /**
     * @return array<array<string>>
     */
    public function provideXpathAssertions(): array
    {
        return [
            ['/View'],
            ['/View[@media-type="application/vnd.ibexa.api.View+xml"]'],
            ['/View[@href="/content/views/test_view"]'],
            ['/View/identifier'],
            ['/View/identifier[text()="test_view"]'],
            ['/View/Query'],
            ['/View/Query[@media-type="application/vnd.ibexa.api.Query+xml"]'],
            ['/View/Result'],
            ['/View/Result[@media-type="application/vnd.ibexa.api.ViewResult+xml"]'],
            ['/View/Result[@href="/content/views/test_view/results"]'],
            ['/View/Result/searchHits/searchHit[@score="0.123" and @index="alexandria"]'],
            ['/View/Result/searchHits/searchHit[@score="0.234" and @index="waze"]'],
        ];
    }

    /**
     * @depends testVisit
     *
     * @dataProvider provideXpathAssertions
     */
    public function testGeneratedXml(string $xpath, DOMDocument $dom): void
    {
        $this->assertXPath($dom, $xpath);
    }

    protected function internalGetVisitor(): ValueObjectVisitor\RestExecutedView
    {
        return new ValueObjectVisitor\RestExecutedView(
            $this->getLocationServiceMock(),
            $this->getRelationListFacadeMock()
        );
    }

    public function getLocationServiceMock(): LocationService&MockObject
    {
        return $this->createMock(LocationService::class);
    }

    private function getRelationListFacadeMock(): ContentService\RelationListFacadeInterface&MockObject
    {
        $relationListFacade = $this->createMock(ContentService\RelationListFacadeInterface::class);
        $relationListFacade->method('getRelations')->willReturnCallback(
            static fn () => yield
        );

        return $relationListFacade;
    }

    public function getContentTypeServiceMock(): ContentTypeService&MockObject
    {
        return $this->createMock(ContentTypeService::class);
    }

    protected function buildContentSearchHit(): SearchHit
    {
        return new SearchHit([
            'score' => 0.123,
            'index' => 'alexandria',
            'valueObject' => new ApiValues\Content([
                'versionInfo' => new Content\VersionInfo([
                    'contentInfo' => new ContentInfo([
                        'mainLocationId' => self::EXAMPLE_LOCATION_ID,
                    ]),
                ]),
                'contentType' => new ContentType(),
            ]),
        ]);
    }

    protected function buildLocationSearchHit(): SearchHit
    {
        return new SearchHit([
            'score' => 0.234,
            'index' => 'waze',
            'valueObject' => new ApiValues\Location(['id' => 10]),
        ]);
    }
}
