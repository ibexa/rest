<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\VersionList;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class VersionListTest extends ValueObjectVisitorBaseTest
{
    public function testVisit(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $versionInfo = new VersionInfo(
            [
                'versionNo' => 1,
                'contentInfo' => new ContentInfo(['id' => 12345]),
            ]
        );
        $versionList = new VersionList([$versionInfo], '/some/path');

        $this->addRouteExpectation(
            'ibexa.rest.load_content_in_version',
            [
                'contentId' => $versionInfo->contentInfo->id,
                'versionNumber' => $versionInfo->versionNo,
            ],
            "/content/objects/{$versionInfo->contentInfo->id}/versions/{$versionInfo->versionNo}"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $versionList
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        return $result;
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsVersionListElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'VersionList',
            ],
            $result,
            'Invalid <VersionList> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsVersionListAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'VersionList',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.VersionList+xml',
                    'href' => '/some/path',
                ],
            ],
            $result,
            'Invalid <VersionList> attributes.',
            false
        );
    }

    public function testVersionListVisitsChildren(): void
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $versionList = new VersionList(
            [
                new VersionInfo(
                    [
                        'contentInfo' => new ContentInfo(
                            [
                                'id' => 42,
                            ]
                        ),
                        'versionNo' => 1,
                    ]
                ),
                new VersionInfo(
                    [
                        'contentInfo' => new ContentInfo(
                            [
                                'id' => 42,
                            ]
                        ),
                        'versionNo' => 2,
                    ]
                ),
            ],
            '/some/path'
        );

        $this->getVisitorMock()->expects(self::exactly(2))
            ->method('visitValueObject')
            ->with(self::isInstanceOf(\Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo::class));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $versionList
        );
    }

    protected function internalGetVisitor(): ValueObjectVisitor\VersionList
    {
        return new ValueObjectVisitor\VersionList();
    }
}
