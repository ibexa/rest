<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;
use Ibexa\Rest\Server\Values;

/**
 * ContentTypeInfoList value object visitor.
 */
class ContentTypeInfoList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Rest\Server\Values\ContentTypeInfoList $data
     */
    public function visit(Visitor $visitor, Generator $generator, mixed $data): void
    {
        $generator->startObjectElement('ContentTypeInfoList');
        $visitor->setHeader('Content-Type', $generator->getMediaType('ContentTypeInfoList'));
        //@todo Needs refactoring, disabling certain headers should not be done this way
        $visitor->setHeader('Accept-Patch', false);

        $generator->startAttribute('href', $data->path);
        $generator->endAttribute('href');

        $generator->startList('ContentType');
        foreach ($data->contentTypes as $contentType) {
            $visitor->visitValueObject(
                new Values\RestContentType($contentType)
            );
        }
        $generator->endList('ContentType');

        $generator->endObjectElement('ContentTypeInfoList');
    }
}
