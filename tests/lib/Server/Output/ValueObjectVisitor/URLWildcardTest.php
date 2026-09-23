<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content;
use Ibexa\Rest\Server\Output\ValueObjectVisitor\URLWildcard;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTestCase;
use PHPUnit\Framework\Attributes\Depends;

class URLWildcardTest extends ValueObjectVisitorBaseTestCase
{
    public function testVisit(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $urlWildcard = new Content\URLWildcard(
            [
                'id' => 42,
                'sourceUrl' => '/source/url',
                'destinationUrl' => '/destination/url',
                'forward' => true,
            ]
        );

        $this->addRouteExpectation(
            'ibexa.rest.load_url_wildcard',
            ['urlWildcardId' => $urlWildcard->id],
            "/content/urlwildcards/{$urlWildcard->id}"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $urlWildcard
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        return $result;
    }

    #[Depends('testVisit')]
    public function testResultContainsUrlWildcardElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'UrlWildcard',
                'children' => [
                    'less_than' => 4,
                    'greater_than' => 2,
                ],
            ],
            $result,
            'Invalid <UrlWildcard> element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsUrlWildcardAttributes(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'UrlWildcard',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.UrlWildcard+xml',
                    'href' => '/content/urlwildcards/42',
                    'id' => '42',
                ],
            ],
            $result,
            'Invalid <UrlWildcard> attributes.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsSourceUrlValueElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'sourceUrl',
                'content' => '/source/url',
            ],
            $result,
            'Invalid or non-existing <UrlWildcard> sourceUrl value element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsDestinationUrlValueElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'destinationUrl',
                'content' => '/destination/url',
            ],
            $result,
            'Invalid or non-existing <UrlWildcard> destinationUrl value element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsForwardValueElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'forward',
                'content' => 'true',
            ],
            $result,
            'Invalid or non-existing <UrlWildcard> forward value element.'
        );
    }

    protected function internalGetVisitor(): URLWildcard
    {
        return new URLWildcard();
    }
}
