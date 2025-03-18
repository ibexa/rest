<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values\ContentType\ContentTypeGroup;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\ContentTypeGroupRefList;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class ContentTypeGroupRefListTest extends ValueObjectVisitorBaseTest
{
    /**
     * @todo coverage test with one group (can't be deleted)
     */
    public function testVisit(): \DOMDocument
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $contentTypeGroupRefList = new ContentTypeGroupRefList(
            new ContentType(
                [
                    'id' => 42,
                    'fieldDefinitions' => [],
                ]
            ),
            [
                new ContentTypeGroup(
                    [
                        'id' => 1,
                    ]
                ),
                new ContentTypeGroup(
                    [
                        'id' => 2,
                    ]
                ),
            ]
        );

        $this->addRouteExpectation(
            'ibexa.rest.list_content_types_for_group',
            ['contentTypeGroupId' => $contentTypeGroupRefList->contentType->id],
            "/content/types/{$contentTypeGroupRefList->contentType->id}/groups"
        );

        // first iteration
        $this->addRouteExpectation(
            'ibexa.rest.load_content_type_group',
            ['contentTypeGroupId' => $contentTypeGroupRefList->contentTypeGroups[0]->id],
            "/content/typegroups/{$contentTypeGroupRefList->contentTypeGroups[0]->id}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.unlink_content_type_from_group',
            [
                'contentTypeId' => $contentTypeGroupRefList->contentType->id,
                'contentTypeGroupId' => $contentTypeGroupRefList->contentTypeGroups[0]->id,
            ],
            "/content/types/{$contentTypeGroupRefList->contentType->id}/groups/{$contentTypeGroupRefList->contentTypeGroups[0]->id}"
        );

        // second iteration
        $this->addRouteExpectation(
            'ibexa.rest.load_content_type_group',
            ['contentTypeGroupId' => $contentTypeGroupRefList->contentTypeGroups[1]->id],
            "/content/typegroups/{$contentTypeGroupRefList->contentTypeGroups[1]->id}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.unlink_content_type_from_group',
            [
                'contentTypeId' => $contentTypeGroupRefList->contentType->id,
                'contentTypeGroupId' => $contentTypeGroupRefList->contentTypeGroups[1]->id,
            ],
            "/content/types/{$contentTypeGroupRefList->contentType->id}/groups/{$contentTypeGroupRefList->contentTypeGroups[1]->id}"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $contentTypeGroupRefList
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
    public function testContentTypeGroupRefListHrefCorrect(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentTypeGroupRefList[@href="/content/types/42/groups"]');
    }

    /**
     * @depends testVisit
     */
    public function testContentTypeGroupRefListMediaTypeCorrect(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentTypeGroupRefList[@media-type="application/vnd.ibexa.api.ContentTypeGroupRefList+xml"]');
    }

    /**
     * @depends testVisit
     */
    public function testFirstContentTypeGroupRefHrefCorrect(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentTypeGroupRefList/ContentTypeGroupRef[1][@href="/content/typegroups/1"]');
    }

    /**
     * @depends testVisit
     */
    public function testFirstContentTypeGroupRefMediaTypeCorrect(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentTypeGroupRefList/ContentTypeGroupRef[1][@media-type="application/vnd.ibexa.api.ContentTypeGroup+xml"]');
    }

    /**
     * @depends testVisit
     */
    public function testFirstContentTypeGroupRefUnlinkHrefCorrect(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentTypeGroupRefList/ContentTypeGroupRef[1]/unlink[@href="/content/types/42/groups/1"]');
    }

    /**
     * @depends testVisit
     */
    public function testFirstContentTypeGroupRefUnlinkMethodCorrect(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentTypeGroupRefList/ContentTypeGroupRef[1]/unlink[@method="DELETE"]');
    }

    /**
     * @depends testVisit
     */
    public function testSecondContentTypeGroupRefHrefCorrect(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentTypeGroupRefList/ContentTypeGroupRef[2][@href="/content/typegroups/2"]');
    }

    /**
     * @depends testVisit
     */
    public function testSecondContentTypeGroupRefMediaTypeCorrect(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentTypeGroupRefList/ContentTypeGroupRef[2][@media-type="application/vnd.ibexa.api.ContentTypeGroup+xml"]');
    }

    /**
     * @depends testVisit
     */
    public function testSecondContentTypeGroupRefUnlinkHrefCorrect(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentTypeGroupRefList/ContentTypeGroupRef[2]/unlink[@href="/content/types/42/groups/2"]');
    }

    /**
     * @depends testVisit
     */
    public function testSecondContentTypeGroupRefUnlinkMethodCorrect(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentTypeGroupRefList/ContentTypeGroupRef[2]/unlink[@method="DELETE"]');
    }

    protected function internalGetVisitor(): ValueObjectVisitor\ContentTypeGroupRefList
    {
        return new ValueObjectVisitor\ContentTypeGroupRefList();
    }
}
