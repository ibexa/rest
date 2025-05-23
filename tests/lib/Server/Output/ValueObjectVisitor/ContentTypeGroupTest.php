<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Core\Repository\Values\ContentType;
use Ibexa\Rest\Server\Output\ValueObjectVisitor\ContentTypeGroup;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class ContentTypeGroupTest extends ValueObjectVisitorBaseTest
{
    public function testVisit(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $contentTypeGroup = new ContentType\ContentTypeGroup(
            [
                'id' => 42,
                'identifier' => 'some-group',
                'creationDate' => new \DateTime('2012-12-31 19:30 Europe/Zagreb'),
                'modificationDate' => new \DateTime('2012-12-31 19:35 Europe/Zagreb'),
                'creatorId' => 14,
                'modifierId' => 13,
                /* @todo uncomment when support for multilingual names and descriptions is added EZP-24776
                'names' => array(
                    'eng-GB' => 'Group name EN',
                    'eng-US' => 'Group name EN US',
                ),
                'descriptions' => array(
                    'eng-GB' => 'Group description EN',
                    'eng-US' => 'Group description EN US',
                ),
                'mainLanguageCode' => 'eng-GB'
                */
            ]
        );

        $routerMock = $this->getRouterMock();

        $this->addRouteExpectation(
            'ibexa.rest.load_content_type_group',
            ['contentTypeGroupId' => $contentTypeGroup->id],
            "/content/typegroups/{$contentTypeGroup->id}"
        );

        $this->addRouteExpectation(
            'ibexa.rest.load_user',
            ['userId' => $contentTypeGroup->creatorId],
            "/user/users/{$contentTypeGroup->creatorId}"
        );

        $this->addRouteExpectation(
            'ibexa.rest.load_user',
            ['userId' => $contentTypeGroup->modifierId],
            "/user/users/{$contentTypeGroup->modifierId}"
        );

        $this->addRouteExpectation(
            'ibexa.rest.list_content_types_for_group',
            ['contentTypeGroupId' => $contentTypeGroup->id],
            "/content/typegroups/{$contentTypeGroup->id}/types"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $contentTypeGroup
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        return $result;
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsContentTypeGroupElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'ContentTypeGroup',
                'children' => [
                    'count' => 7,
                ],
            ],
            $result,
            'Invalid <ContentTypeGroup> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsContentTypeGroupAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'ContentTypeGroup',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.ContentTypeGroup+xml',
                    'href' => '/content/typegroups/42',
                ],
            ],
            $result,
            'Invalid <ContentTypeGroup> attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsIdValueElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'id',
                'content' => '42',
            ],
            $result,
            'Invalid or non-existing <ContentTypeGroup> id value element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsIdentifierValueElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'identifier',
                'content' => 'some-group',
            ],
            $result,
            'Invalid or non-existing <ContentTypeGroup> identifier value element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsCreatedValueElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'created',
                'content' => '2012-12-31T19:30:00+01:00',
            ],
            $result,
            'Invalid or non-existing <ContentTypeGroup> created value element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsModifiedValueElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'modified',
                'content' => '2012-12-31T19:35:00+01:00',
            ],
            $result,
            'Invalid or non-existing <ContentTypeGroup> modified value element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsCreatorElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Creator',
            ],
            $result,
            'Invalid <Creator> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsCreatorAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Creator',
                'attributes' => [
                    'href' => '/user/users/14',
                    'media-type' => 'application/vnd.ibexa.api.User+xml',
                ],
            ],
            $result,
            'Invalid <Creator> element attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsModifierElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Modifier',
            ],
            $result,
            'Invalid <Modifier> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsModifierAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Modifier',
                'attributes' => [
                    'href' => '/user/users/13',
                    'media-type' => 'application/vnd.ibexa.api.User+xml',
                ],
            ],
            $result,
            'Invalid <Modifier> element attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsContentTypesElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'ContentTypes',
            ],
            $result,
            'Invalid <ContentTypes> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsContentTypesAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'ContentTypes',
                'attributes' => [
                    'href' => '/content/typegroups/42/types',
                    'media-type' => 'application/vnd.ibexa.api.ContentTypeInfoList+xml',
                ],
            ],
            $result,
            'Invalid <ContentTypes> attributes.',
            false
        );
    }

    protected function internalGetVisitor(): ContentTypeGroup
    {
        return new ContentTypeGroup();
    }
}
