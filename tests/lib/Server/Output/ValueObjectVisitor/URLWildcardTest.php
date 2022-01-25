<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class URLWildcardTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the URLWildcard visitor.
     *
     * @return string
     */
    public function testVisit()
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

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains UrlWildcard element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsUrlWildcardElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'UrlWildcard',
                'children' => [
                    'less_than' => 4,
                    'greater_than' => 2,
                ],
            ],
            $result,
            'Invalid <UrlWildcard> element.',
            false
        );
    }

    /**
     * Test if result contains UrlWildcard element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsUrlWildcardAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'UrlWildcard',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.UrlWildcard+xml',
                    'href' => '/content/urlwildcards/42',
                    'id' => '42',
                ],
            ],
            $result,
            'Invalid <UrlWildcard> attributes.',
            false
        );
    }

    /**
     * Test if result contains sourceUrl value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsSourceUrlValueElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'sourceUrl',
                'content' => '/source/url',
            ],
            $result,
            'Invalid or non-existing <UrlWildcard> sourceUrl value element.',
            false
        );
    }

    /**
     * Test if result contains destinationUrl value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsDestinationUrlValueElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'destinationUrl',
                'content' => '/destination/url',
            ],
            $result,
            'Invalid or non-existing <UrlWildcard> destinationUrl value element.',
            false
        );
    }

    /**
     * Test if result contains forward value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsForwardValueElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'forward',
                'content' => 'true',
            ],
            $result,
            'Invalid or non-existing <UrlWildcard> forward value element.',
            false
        );
    }

    /**
     * Get the URLWildcard visitor.
     *
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\URLWildcard
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\URLWildcard();
    }
}

class_alias(URLWildcardTest::class, 'EzSystems\EzPlatformRest\Tests\Server\Output\ValueObjectVisitor\URLWildcardTest');
