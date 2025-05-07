<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Core\Repository\Values\Content;
use Ibexa\Rest\Server\Output\ValueObjectVisitor\VersionInfo;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class VersionInfoTest extends ValueObjectVisitorBaseTest
{
    protected \DateTime $creationDate;

    protected \DateTime $modificationDate;

    public function setUp(): void
    {
        $this->creationDate = new \DateTime('2012-05-19 12:23 Europe/Berlin');
        $this->modificationDate = new \DateTime('2012-08-31 23:42 Europe/Berlin');
    }

    public function testVisit(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $versionInfo = new Content\VersionInfo(
            [
                'id' => 23,
                'versionNo' => 5,
                'status' => Content\VersionInfo::STATUS_PUBLISHED,
                'creationDate' => $this->creationDate,
                'creatorId' => 14,
                'modificationDate' => $this->modificationDate,
                'initialLanguageCode' => 'eng-US',
                'languageCodes' => ['eng-US', 'ger-DE'],
                'names' => [
                    'eng-US' => 'Sindelfingen',
                    'eng-GB' => 'Bielefeld',
                ],
                'contentInfo' => new ContentInfo(['id' => 42]),
            ]
        );

        $this->addRouteExpectation(
            'ibexa.rest.load_user',
            ['userId' => $versionInfo->creatorId],
            "/user/users/{$versionInfo->creatorId}"
        );

        $this->addRouteExpectation(
            'ibexa.rest.load_content',
            ['contentId' => $versionInfo->contentInfo->id],
            "/content/objects/{$versionInfo->contentInfo->id}"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $versionInfo
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        return $result;
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsVersionInfoChildren(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'VersionInfo',
                'children' => [
                    'less_than' => 11,
                    'greater_than' => 9,
                ],
            ],
            $result,
            'Invalid <VersionInfo> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testVersionInfoIdElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'id',
                'content' => '23',
            ],
            $result,
            'Invalid <id> value.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testVersionInfoVersionNoElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'versionNo',
                'content' => '5',
            ],
            $result,
            'Invalid <versionNo> value.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testVersionInfoStatusElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'status',
                'content' => 'PUBLISHED',
            ],
            $result,
            'Invalid <status> value.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testVersionInfoCreationDateElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'creationDate',
                'content' => $this->creationDate->format('c'),
            ],
            $result,
            'Invalid <creationDate> value.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testVersionInfoModificationDateElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'modificationDate',
                'content' => $this->modificationDate->format('c'),
            ],
            $result,
            'Invalid <modificationDate> value.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testVersionInfoInitialLanguageCodeElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'initialLanguageCode',
                'content' => 'eng-US',
            ],
            $result,
            'Invalid <initialLanguageCode> value.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testVersionInfoLanguageCodesElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'languageCodes',
                'content' => 'eng-US,ger-DE',
            ],
            $result,
            'Invalid <languageCodes> value.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testVersionInfoNamesElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'names',
                'children' => [
                    'less_than' => 3,
                    'greater_than' => 1,
                ],
            ],
            $result,
            'Invalid <names> value.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testVersionInfoContentElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Content',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.ContentInfo+xml',
                    'href' => '/content/objects/42',
                ],
            ],
            $result,
            'Invalid <initialLanguageCode> value.',
            false
        );
    }

    protected function internalGetVisitor(): VersionInfo
    {
        return new VersionInfo();
    }
}
