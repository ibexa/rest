<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;

/**
 * ContentList value object visitor.
 */
class ContentList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Rest\Server\Values\ContentList $data
     */
    public function visit(Visitor $visitor, Generator $generator, mixed $data): void
    {
        $generator->startObjectElement('ContentList');
        $visitor->setHeader('Content-Type', $generator->getMediaType('ContentList'));
        //@todo Needs refactoring, disabling certain headers should not be done this way
        $visitor->setHeader('Accept-Patch', false);

        $generator->startAttribute(
            'href',
            $this->router->generate('ibexa.rest.redirect_content')
        );
        $generator->endAttribute('href');

        $generator->startAttribute('totalCount', $data->totalCount);
        $generator->endAttribute('totalCount');

        $generator->startList('ContentInfo');
        foreach ($data->contents as $content) {
            $visitor->visitValueObject($content);
        }
        $generator->endList('ContentInfo');

        $generator->endObjectElement('ContentList');
    }
}
