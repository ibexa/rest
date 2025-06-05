<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values;
use Ibexa\Rest\Output\FieldTypeSerializer;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\Version;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;
use PHPUnit\Framework\MockObject\MockObject;

class VersionTest extends ValueObjectVisitorBaseTest
{
    protected FieldTypeSerializer&MockObject $fieldTypeSerializerMock;

    public function setUp(): void
    {
        $this->fieldTypeSerializerMock = $this->createMock(FieldTypeSerializer::class);
    }

    public function testVisit(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $version = new Version(
            new Values\Content\Content(
                [
                    'versionInfo' => new Values\Content\VersionInfo(
                        [
                            'versionNo' => 5,
                            'contentInfo' => new ContentInfo(
                                [
                                    'id' => 23,
                                    'contentTypeId' => 42,
                                ]
                            ),
                        ]
                    ),
                    'internalFields' => [
                        new Field(
                            [
                                'id' => 1,
                                'languageCode' => 'eng-US',
                                'fieldDefIdentifier' => 'ibexa_author',
                                'fieldTypeIdentifier' => 'ibexa_author',
                            ]
                        ),
                        new Field(
                            [
                                'id' => 2,
                                'languageCode' => 'eng-US',
                                'fieldDefIdentifier' => 'ibexa_image',
                                'fieldTypeIdentifier' => 'ibexa_author',
                            ]
                        ),
                    ],
                ]
            ),
            $this->getMockForAbstractClass(ContentType::class),
            []
        );

        $this->addRouteExpectation(
            'ibexa.rest.load_content_in_version',
            [
                'contentId' => $version->content->id,
                'versionNumber' => $version->content->versionInfo->versionNo,
            ],
            "/content/objects/{$version->content->id}/versions/{$version->content->versionInfo->versionNo}"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $version
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        return $result;
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsVersionChildren(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Version',
                'children' => [
                    'less_than' => 2,
                    'greater_than' => 0,
                ],
            ],
            $result,
            'Invalid <Version> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultVersionAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Version',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.Version+xml',
                    'href' => '/content/objects/23/versions/5',
                ],
            ],
            $result,
            'Invalid <Version> attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsFieldsChildren(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Fields',
                'children' => [
                    'less_than' => 3,
                    'greater_than' => 1,
                ],
            ],
            $result,
            'Invalid <Fields> element.',
            false
        );
    }

    protected function internalGetVisitor(): ValueObjectVisitor\Version
    {
        return new ValueObjectVisitor\Version($this->fieldTypeSerializerMock);
    }
}
