<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\URLAlias;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\URLAliasRefList;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class URLAliasRefListTest extends ValueObjectVisitorBaseTest
{
    public function testVisit(): \DOMDocument
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $urlAliasRefList = new URLAliasRefList(
            [
                new URLAlias(
                    [
                        'id' => 'some-id',
                    ]
                ),
            ],
            '/some/path'
        );

        $this->addRouteExpectation(
            'ibexa.rest.load_url_alias',
            ['urlAliasId' => $urlAliasRefList->urlAliases[0]->id],
            "/content/urlaliases/{$urlAliasRefList->urlAliases[0]->id}"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $urlAliasRefList
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        $dom = new \DOMDocument();
        $dom->loadXml($result);

        return $dom;
    }

    /**
     * @depends testVisit
     */
    public function testUrlAliasRefListHrefCorrect(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/UrlAliasRefList[@href="/some/path"]');
    }

    /**
     * @depends testVisit
     */
    public function testUrlAliasRefListMediaTypeCorrect(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/UrlAliasRefList[@media-type="application/vnd.ibexa.api.UrlAliasRefList+xml"]');
    }

    /**
     * @depends testVisit
     */
    public function testUrlAliasHrefCorrect(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/UrlAliasRefList/UrlAlias[@href="/content/urlaliases/some-id"]');
    }

    /**
     * @depends testVisit
     */
    public function testUrlAliasMediaTypeCorrect(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/UrlAliasRefList/UrlAlias[@media-type="application/vnd.ibexa.api.UrlAlias+xml"]');
    }

    protected function internalGetVisitor(): ValueObjectVisitor\URLAliasRefList
    {
        return new ValueObjectVisitor\URLAliasRefList();
    }
}
