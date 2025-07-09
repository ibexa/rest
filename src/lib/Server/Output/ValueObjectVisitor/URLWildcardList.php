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
 * URLWildcardList value object visitor.
 */
class URLWildcardList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Rest\Server\Values\URLWildcardList $data
     */
    public function visit(Visitor $visitor, Generator $generator, mixed $data): void
    {
        $generator->startObjectElement('UrlWildcardList');
        $visitor->setHeader('Content-Type', $generator->getMediaType('UrlWildcardList'));

        $generator->startAttribute(
            'href',
            $this->router->generate('ibexa.rest.list_url_wildcards')
        );
        $generator->endAttribute('href');

        $generator->startList('UrlWildcard');
        foreach ($data->urlWildcards as $urlWildcard) {
            $visitor->visitValueObject($urlWildcard);
        }
        $generator->endList('UrlWildcard');

        $generator->endObjectElement('UrlWildcardList');
    }
}
