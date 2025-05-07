<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Rest\Output\ValueObjectVisitor;
use Ibexa\Rest\Values\ContentObjectStates;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class ContentObjectStatesTest extends ValueObjectVisitorBaseTest
{
    public function testVisit(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        // @todo Improve this test with values...
        $stateList = new ContentObjectStates([]);

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $stateList
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        return $result;
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsContentObjectStatesElement(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'ContentObjectStates',
            ],
            $result,
            'Invalid <ContentObjectStates> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsContentObjectStatesAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'ContentObjectStates',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.ContentObjectStates+xml',
                ],
            ],
            $result,
            'Invalid <ContentObjectStates> attributes.',
            false
        );
    }

    protected function internalGetVisitor(): ValueObjectVisitor\ContentObjectStates
    {
        return new ValueObjectVisitor\ContentObjectStates();
    }
}
