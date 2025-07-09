<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;
use Ibexa\Rest\Server\Values\BookmarkList as BookmarkListValue;

class BookmarkList extends ValueObjectVisitor
{
    /**
     * @param \Ibexa\Rest\Server\Values\BookmarkList $data
     */
    public function visit(Visitor $visitor, Generator $generator, mixed $data): void
    {
        $generator->startObjectElement('BookmarkList');
        $visitor->setHeader('Content-Type', $generator->getMediaType('BookmarkList'));

        $this->visitAttributes($visitor, $generator, $data);
        $generator->endObjectElement('BookmarkList');
    }

    protected function visitAttributes(Visitor $visitor, Generator $generator, BookmarkListValue $data): void
    {
        $generator->startValueElement('count', $data->totalCount);
        $generator->endValueElement('count');

        $generator->startList('items');
        foreach ($data->items as $restLocation) {
            $generator->startObjectElement('Bookmark');

            $generator->startAttribute('_href', $this->router->generate('ibexa.rest.is_bookmarked', [
                'locationId' => $restLocation->location->id,
            ]));
            $generator->endAttribute('_href');

            $visitor->visitValueObject($restLocation);
            $generator->endObjectElement('Bookmark');
        }

        $generator->endList('items');
    }
}
