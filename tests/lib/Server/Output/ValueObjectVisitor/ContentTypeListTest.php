<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Core\Repository\Values\ContentType;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\ContentTypeList;
use Ibexa\Rest\Server\Values\RestContentType;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class ContentTypeListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the ContentTypeList visitor.
     */
    public function testVisit(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $contentTypeList = new ContentTypeList([], '/content/typegroups/2/types');

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $contentTypeList
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        return $result;
    }

    /**
     * Test if result contains ContentTypeList element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentTypeListElement($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'ContentTypeList',
            ],
            $result,
            'Invalid <ContentTypeList> element.',
            false
        );
    }

    /**
     * Test if result contains ContentTypeList element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentTypeListAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'ContentTypeList',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.ContentTypeList+xml',
                    'href' => '/content/typegroups/2/types',
                ],
            ],
            $result,
            'Invalid <ContentTypeList> attributes.',
            false
        );
    }

    /**
     * Test if ContentTypeList visitor visits the children.
     */
    public function testContentTypeListVisitsChildren(): void
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $contentTypeList = new ContentTypeList(
            [
                new ContentType\ContentType(
                    [
                        'fieldDefinitions' => new ContentType\FieldDefinitionCollection([]),
                    ]
                ),
                new ContentType\ContentType(
                    [
                        'fieldDefinitions' => new ContentType\FieldDefinitionCollection([]),
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
            $contentTypeList
        );
    }

    /**
     * Get the ContentTypeList visitor.
     */
    protected function internalGetVisitor(): ValueObjectVisitor\ContentTypeList
    {
        return new ValueObjectVisitor\ContentTypeList();
    }
}
