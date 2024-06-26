<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Core\Repository\Values\Content;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class VersionInfoTest extends ValueObjectVisitorBaseTest
{
    /**
     * @var \DateTime
     */
    protected $creationDate;

    /**
     * @var \DateTime
     */
    protected $modificationDate;

    public function setUp(): void
    {
        $this->creationDate = new \DateTime('2012-05-19 12:23 Europe/Berlin');
        $this->modificationDate = new \DateTime('2012-08-31 23:42 Europe/Berlin');
    }

    /**
     * Test the VersionInfo visitor.
     *
     * @return string
     */
    public function testVisit()
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

        self::assertNotNull($result);

        return $result;
    }

    /**
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsVersionInfoChildren($result)
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
     * @param string $result
     *
     * @depends testVisit
     */
    public function testVersionInfoIdElement($result)
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
     * @param string $result
     *
     * @depends testVisit
     */
    public function testVersionInfoVersionNoElement($result)
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
     * @param string $result
     *
     * @depends testVisit
     */
    public function testVersionInfoStatusElement($result)
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
     * @param string $result
     *
     * @depends testVisit
     */
    public function testVersionInfoCreationDateElement($result)
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
     * @param string $result
     *
     * @depends testVisit
     */
    public function testVersionInfoModificationDateElement($result)
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
     * @param string $result
     *
     * @depends testVisit
     */
    public function testVersionInfoInitialLanguageCodeElement($result)
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
     * @param string $result
     *
     * @depends testVisit
     */
    public function testVersionInfoLanguageCodesElement($result)
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
     * @param string $result
     *
     * @depends testVisit
     */
    public function testVersionInfoNamesElement($result)
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
     * @param string $result
     *
     * @depends testVisit
     */
    public function testVersionInfoContentElement($result)
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

    /**
     * Get the VersionInfo visitor.
     *
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\VersionInfo
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\VersionInfo();
    }
}
