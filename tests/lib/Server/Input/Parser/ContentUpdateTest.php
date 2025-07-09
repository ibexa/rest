<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser;

use DateTime;
use Ibexa\Contracts\Rest\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Rest\Server\Input\Parser\ContentUpdate;
use Ibexa\Rest\Server\Input\Parser\ContentUpdate as ContentUpdateParser;
use Ibexa\Rest\Values\RestContentMetadataUpdateStruct;

class ContentUpdateTest extends BaseTest
{
    public function testParseValid(): RestContentMetadataUpdateStruct
    {
        $inputArray = $this->getValidInputData();

        $contentUpdateParser = $this->getParser();
        $result = $contentUpdateParser->parse(
            $inputArray,
            $this->getParsingDispatcherMock()
        );

        self::assertInstanceOf(
            RestContentMetadataUpdateStruct::class,
            $result,
        );

        self::assertEquals(
            $this->getContentUpdateStruct(),
            $result
        );

        return $result;
    }

    /**
     * @depends testParseValid
     */
    public function testParserResultOwner(RestContentMetadataUpdateStruct $result): void
    {
        self::assertEquals(
            '42',
            $result->ownerId
        );
    }

    /**
     * @dataProvider providerForTestParseFailureInvalidHref
     */
    public function testParseFailureInvalidHref(string $element, string $exceptionMessage): void
    {
        $inputArray = $this->getValidInputData();
        $inputArray[$element]['_href'] = '/invalid/section/uri';

        $contentUpdateParser = $this->getParser();

        try {
            $contentUpdateParser->parse(
                $inputArray,
                $this->getParsingDispatcherMock()
            );
        } catch (Parser $e) {
            if ($e->getMessage() != $exceptionMessage) {
                self::fail("Failed asserting that exception message '" . $e->getMessage() . "' contains '$exceptionMessage'.");
            }
            $exceptionThrown = true;
        }

        if (!isset($exceptionThrown)) {
            self::fail('Failed asserting that exception of type "\\Ibexa\\Core\\REST\\Common\\Exceptions\\Parser" is thrown.');
        }
    }

    /**
     * @return array<array{0: string, 1: string}>
     */
    public function providerForTestParseFailureInvalidHref(): array
    {
        return [
            ['Section', 'Invalid format for the <Section> reference in <ContentUpdate>.'],
            ['MainLocation', 'Invalid format for the <MainLocation> reference in <ContentUpdate>.'],
            ['Owner', 'Invalid format for the <Owner> reference in <ContentUpdate>.'],
        ];
    }

    /**
     * @dataProvider providerForTestParseFailureInvalidDate
     */
    public function testParseFailureInvalidDate(string $element, string $exceptionMessage): void
    {
        $inputArray = $this->getValidInputData();
        $inputArray[$element] = 42;

        $contentUpdateParser = $this->getParser();

        try {
            $contentUpdateParser->parse(
                $inputArray,
                $this->getParsingDispatcherMock()
            );
        } catch (Parser $e) {
            if ($e->getMessage() != $exceptionMessage) {
                self::fail("Failed asserting that exception message '" . $e->getMessage() . "' contains '$exceptionMessage'.");
            }
            $exceptionThrown = true;
        }

        if (!isset($exceptionThrown)) {
            self::fail('Failed asserting that exception of type "\\Ibexa\\Core\\REST\\Common\\Exceptions\\Parser" is thrown.');
        }
    }

    /**
     * @return array<array{0: string, 1: string}>
     */
    public function providerForTestParseFailureInvalidDate(): array
    {
        return [
            ['publishDate', 'Invalid format for <publishDate> in <ContentUpdate>'],
            ['modificationDate', 'Invalid format for <modificationDate> in <ContentUpdate>'],
        ];
    }

    protected function internalGetParser(): ContentUpdate
    {
        return new ContentUpdateParser();
    }

    protected function getContentUpdateStruct(): RestContentMetadataUpdateStruct
    {
        return new RestContentMetadataUpdateStruct(
            [
                'mainLanguageCode' => 'eng-GB',
                'sectionId' => 23,
                'mainLocationId' => 55,
                'ownerId' => 42,
                'alwaysAvailable' => false,
                'remoteId' => '7e7afb135e50490a281dafc0aafb6dac',
                'modificationDate' => new DateTime('19/Sept/2012:14:05:00 +0200'),
                'publishedDate' => new DateTime('19/Sept/2012:14:05:00 +0200'),
            ]
        );
    }

    protected function getValidInputData(): array
    {
        return [
            'mainLanguageCode' => 'eng-GB',
            'Section' => ['_href' => '/content/sections/23'],
            'MainLocation' => ['_href' => '/content/locations/1/2/55'],
            'Owner' => ['_href' => '/user/users/42'],
            'alwaysAvailable' => 'false',
            'remoteId' => '7e7afb135e50490a281dafc0aafb6dac',
            'modificationDate' => '19/Sept/2012:14:05:00 +0200',
            'publishDate' => '19/Sept/2012:14:05:00 +0200',
        ];
    }

    public function getParseHrefExpectationsMap(): array
    {
        return [
            ['/content/sections/23', 'sectionId', 23],
            ['/user/users/42', 'userId', 42],
            ['/content/locations/1/2/55', 'locationPath', '1/2/55'],

            ['/invalid/section/uri', 'sectionId', new InvalidArgumentException('Invalid format for the <Section> reference in <ContentUpdate>.')],
            ['/invalid/section/uri', 'userId', new InvalidArgumentException('Invalid format for the <Owner> reference in <ContentUpdate>.')],
            ['/invalid/section/uri', 'locationPath', new InvalidArgumentException('Invalid format for the <MainLocation> reference in <ContentUpdate>.')],
        ];
    }
}
