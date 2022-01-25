<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\URLAliasList;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class URLAliasListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the URLAliasList visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $urlAliasList = new URLAliasList([], '/content/urlaliases');

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $urlAliasList
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains UrlAliasList element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsUrlAliasListElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'UrlAliasList',
            ],
            $result,
            'Invalid <UrlAliasList> element.',
            false
        );
    }

    /**
     * Test if result contains UrlAliasList element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsUrlAliasListAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'UrlAliasList',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.UrlAliasList+xml',
                    'href' => '/content/urlaliases',
                ],
            ],
            $result,
            'Invalid <UrlAliasList> attributes.',
            false
        );
    }

    /**
     * Test if URLAliasList visitor visits the children.
     */
    public function testURLAliasListVisitsChildren()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $urlAliasList = new URLAliasList(
            [
                new Content\URLAlias(),
                new Content\URLAlias(),
            ],
            '/content/urlaliases'
        );

        $this->getVisitorMock()->expects($this->exactly(2))
            ->method('visitValueObject')
            ->with($this->isInstanceOf(Content\URLAlias::class));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $urlAliasList
        );
    }

    /**
     * Get the URLAliasList visitor.
     *
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\URLAliasList
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\URLAliasList();
    }
}

class_alias(URLAliasListTest::class, 'EzSystems\EzPlatformRest\Tests\Server\Output\ValueObjectVisitor\URLAliasListTest');
