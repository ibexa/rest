<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Core\Repository\Values\ContentType;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\ContentTypeInfoList;
use Ibexa\Rest\Server\Values\RestContentType;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class ContentTypeInfoListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the ContentTypeInfoList visitor.
     */
    public function testVisit(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $contentTypeInfoList = new ContentTypeInfoList([], '/content/typegroups/2/types');

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $contentTypeInfoList
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        return $result;
    }

    /**
     * Test if result contains ContentTypeInfoList element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentTypeInfoListElement($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'ContentTypeInfoList',
            ],
            $result,
            'Invalid <ContentTypeInfoList> element.',
            false
        );
    }

    /**
     * Test if result contains ContentTypeInfoList element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentTypeInfoListAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'ContentTypeInfoList',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.ContentTypeInfoList+xml',
                    'href' => '/content/typegroups/2/types',
                ],
            ],
            $result,
            'Invalid <ContentTypeInfoList> attributes.',
            false
        );
    }

    /**
     * Test if ContentTypeInfoList visitor visits the children.
     */
    public function testContentTypeInfoListVisitsChildren(): void
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $contentTypeInfoList = new ContentTypeInfoList(
            [
                new ContentType\ContentType(
                    [
                        'fieldDefinitions' => new ContentType\FieldDefinitionCollection(),
                    ]
                ),
                new ContentType\ContentType(
                    [
                        'fieldDefinitions' => new ContentType\FieldDefinitionCollection(),
                    ]
                ),
            ],
            '/content/typegroups/2/types'
        );

        $this->getVisitorMock()->expects(self::exactly(2))
            ->method('visitValueObject')
            ->with(self::isInstanceOf(RestContentType::class));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $contentTypeInfoList
        );
    }

    /**
     * Get the ContentTypeInfoList visitor.
     */
    protected function internalGetVisitor(): ValueObjectVisitor\ContentTypeInfoList
    {
        return new ValueObjectVisitor\ContentTypeInfoList();
    }
}
