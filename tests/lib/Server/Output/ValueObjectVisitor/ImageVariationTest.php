<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Variation\Values\ImageVariation;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class ImageVariationTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the ImageVariation visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $imageVariation = new ImageVariation(
            [
                'width' => 600,
                'height' => 400,
                'name' => 'test',
                'imageId' => '123-456789',
                'uri' => '/path/to/image/123/456789/variation.png',
                'mimeType' => 'image/png',
                'fileSize' => 12345,
                'fileName' => 'Test-Image.png',
            ]
        );

        $this->addRouteExpectation(
            'ibexa.rest.binary_content.get_image_variation',
            [
                'imageId' => '123-456789',
                'variationIdentifier' => 'test',
            ],
            "/content/binary/images/{$imageVariation->imageId}/variations/{$imageVariation->name}"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $imageVariation
        );

        $result = $generator->endDocument(null);

        self::assertNotNull($result);

        $dom = new \DOMDocument();
        $dom->loadXml($result);

        return $dom;
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testContentImageVariationContentTagExists(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/ContentImageVariation');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testContentImageVariationTagHrefAttribute(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/ContentImageVariation[@href="/content/binary/images/123-456789/variations/test"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testContentImageVariationTagMediaTypeAttribute(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/ContentImageVariation[@media-type="application/vnd.ibexa.api.ContentImageVariation+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testUriTagExists(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/ContentImageVariation/uri');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testUriTagValue(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/ContentImageVariation/uri[text()="/path/to/image/123/456789/variation.png"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testContentTypeTagExists(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/ContentImageVariation/contentType');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testContentTypeTagValue(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/ContentImageVariation/contentType[text()="image/png"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testWidthTagExists(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/ContentImageVariation/width');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testWidthTagValue(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/ContentImageVariation/width[text()="600"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testHeightTagExists(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/ContentImageVariation/height');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testHeightTagValue(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/ContentImageVariation/height[text()="400"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testFileSizeTagExists(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/ContentImageVariation/fileSize');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testFileSizeTagValue(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/ContentImageVariation/fileSize[text()="12345"]');
    }

    /**
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\ImageVariation
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\ImageVariation();
    }
}
