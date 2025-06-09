<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Core\Repository\Values;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\FieldDefinitionList;
use Ibexa\Rest\Server\Values\RestContentType;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

/**
 * @todo coverage add unit test for testVisitDraftType
 * @todo coverage cover fieldDefinitions (with Mock of Output\Visitor)
 */
class RestContentTypeTest extends ValueObjectVisitorBaseTest
{
    public function testVisitDefinedType(): \DOMDocument
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $restContentType = $this->getBasicContentType();

        $this->getVisitorMock()->expects(self::once())
            ->method('visitValueObject')
            ->with(self::isInstanceOf(FieldDefinitionList::class));

        $this->addRouteExpectation(
            'ibexa.rest.load_content_type',
            ['contentTypeId' => $restContentType->contentType->id],
            "/content/types/{$restContentType->contentType->id}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_user',
            ['userId' => $restContentType->contentType->creatorId],
            "/user/users/{$restContentType->contentType->creatorId}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_user',
            ['userId' => $restContentType->contentType->modifierId],
            "/user/users/{$restContentType->contentType->modifierId}"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_groups_of_content_type',
            ['contentTypeId' => $restContentType->contentType->id],
            "/content/types/{$restContentType->contentType->id}/groups"
        );
        $this->addRouteExpectation(
            'ibexa.rest.load_content_type_draft',
            ['contentTypeId' => $restContentType->contentType->id],
            "/content/types/{$restContentType->contentType->id}/draft"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $restContentType
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        $dom = new \DOMDocument();
        $dom->loadXml($result);

        return $dom;
    }

    protected function getBasicContentType(): RestContentType
    {
        return new RestContentType(
            new Values\ContentType\ContentType(
                [
                    'id' => 123,
                    'status' => Values\ContentType\ContentType::STATUS_DEFINED,
                    'identifier' => 'contentTypeIdentifier',
                    'creationDate' => new \DateTime('2012-09-06 19:30 Europe/Berlin'),
                    'modificationDate' => new \DateTime('2012-09-06 19:32 Europe/Berlin'),
                    'creatorId' => 123,
                    'modifierId' => 34,
                    'remoteId' => 'remoteId',
                    'urlAliasSchema' => 'urlAliasSchema',
                    'nameSchema' => 'nameSchema',
                    'isContainer' => true,
                    'mainLanguageCode' => 'eng-US',
                    'defaultAlwaysAvailable' => false,
                    'defaultSortField' => Values\Content\Location::SORT_FIELD_SECTION,
                    'defaultSortOrder' => Values\Content\Location::SORT_ORDER_DESC,

                    'names' => ['eng-US' => 'Sindelfingen', 'eng-GB' => 'Bielefeld'],
                    'descriptions' => ['eng-GB' => 'Sindelfingen', 'eng-US' => 'Bielefeld'],

                    // "Mock"
                    'fieldDefinitions' => new Values\ContentType\FieldDefinitionCollection(),
                ]
            ),
            []
        );
    }

    /**
     * @depends testVisitDefinedType
     */
    public function testContentTypeHref(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentType[@href="/content/types/contentTypeId"]');
    }

    /**
     * @depends testVisitDefinedType
     */
    public function testContentTypeMediaType(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentType[@media-type="application/vnd.ibexa.api.ContentType+xml"]');
    }

    /**
     * @depends testVisitDefinedType
     */
    public function testId(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentType/id[text()="contentTypeId"]');
    }

    /**
     * @depends testVisitDefinedType
     */
    public function testStatus(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentType/status[text()="DEFINED"]');
    }

    /**
     * @depends testVisitDefinedType
     */
    public function testIdentifier(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentType/identifier[text()="contentTypeIdentifier"]');
    }

    /**
     * @depends testVisitDefinedType
     */
    public function testFirstName(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentType/names/value[@languageCode="eng-US" and text()="Sindelfingen"]');
    }

    /**
     * @depends testVisitDefinedType
     */
    public function testSecondName(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentType/names/value[@languageCode="eng-GB" and text()="Bielefeld"]');
    }

    /**
     * @depends testVisitDefinedType
     */
    public function testFirstDescription(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentType/descriptions/value[@languageCode="eng-GB" and text()="Sindelfingen"]');
    }

    /**
     * @depends testVisitDefinedType
     */
    public function testSecondDescription(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentType/descriptions/value[@languageCode="eng-US" and text()="Bielefeld"]');
    }

    /**
     * @depends testVisitDefinedType
     */
    public function testCreationDate(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentType/creationDate[text()="2012-09-06T19:30:00+02:00"]');
    }

    /**
     * @depends testVisitDefinedType
     */
    public function testModificationDate(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentType/modificationDate[text()="2012-09-06T19:32:00+02:00"]');
    }

    /**
     * @depends testVisitDefinedType
     */
    public function testCreatorHref(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentType/Creator[@href="/user/users/creatorId"]');
    }

    /**
     * @depends testVisitDefinedType
     */
    public function testCreatorMediaType(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentType/Creator[@media-type="application/vnd.ibexa.api.User+xml"]');
    }

    /**
     * @depends testVisitDefinedType
     */
    public function testModifierHref(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentType/Modifier[@href="/user/users/modifierId"]');
    }

    /**
     * @depends testVisitDefinedType
     */
    public function testModifierMediaType(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentType/Modifier[@media-type="application/vnd.ibexa.api.User+xml"]');
    }

    /**
     * @depends testVisitDefinedType
     */
    public function testDraftHref(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentType/Draft[@href="/content/types/contentTypeId/draft"]');
    }

    /**
     * @depends testVisitDefinedType
     */
    public function testDraftType(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentType/Draft[@media-type="application/vnd.ibexa.api.ContentType+xml"]');
    }

    /**
     * @depends testVisitDefinedType
     */
    public function testGroupsHref(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentType/Groups[@href="/content/types/contentTypeId/groups"]');
    }

    /**
     * @depends testVisitDefinedType
     */
    public function testGroupsType(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentType/Groups[@media-type="application/vnd.ibexa.api.ContentTypeGroupRefList+xml"]');
    }

    /**
     * @depends testVisitDefinedType
     */
    public function testRemoteId(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentType/remoteId[text()="remoteId"]');
    }

    /**
     * @depends testVisitDefinedType
     */
    public function testUrlAliasSchema(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentType/urlAliasSchema[text()="urlAliasSchema"]');
    }

    /**
     * @depends testVisitDefinedType
     */
    public function testNameSchema(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentType/nameSchema[text()="nameSchema"]');
    }

    /**
     * @depends testVisitDefinedType
     */
    public function testIsContainer(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentType/isContainer[text()="true"]');
    }

    /**
     * @depends testVisitDefinedType
     */
    public function testMainLanguageCode(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentType/mainLanguageCode[text()="eng-US"]');
    }

    /**
     * @depends testVisitDefinedType
     */
    public function testDefaultAlwaysAvailable(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentType/defaultAlwaysAvailable[text()="false"]');
    }

    /**
     * @depends testVisitDefinedType
     */
    public function testDefaultSortField(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentType/defaultSortField[text()="SECTION"]');
    }

    /**
     * @depends testVisitDefinedType
     */
    public function testDefaultSortOrder(\DOMDocument $dom): void
    {
        $this->assertXPath($dom, '/ContentType/defaultSortOrder[text()="DESC"]');
    }

    protected function internalGetVisitor(): ValueObjectVisitor\RestContentType
    {
        return new ValueObjectVisitor\RestContentType();
    }
}
