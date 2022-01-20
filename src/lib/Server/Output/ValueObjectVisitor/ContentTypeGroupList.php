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
 * ContentTypeGroupList value object visitor.
 */
class ContentTypeGroupList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Contracts\Rest\Output\Visitor $visitor
     * @param \Ibexa\Contracts\Rest\Output\Generator $generator
     * @param \Ibexa\Rest\Server\Values\ContentTypeGroupList $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('ContentTypeGroupList');
        $visitor->setHeader('Content-Type', $generator->getMediaType('ContentTypeGroupList'));
        //@todo Needs refactoring, disabling certain headers should not be done this way
        $visitor->setHeader('Accept-Patch', false);

        $generator->startAttribute(
            'href',
            $this->router->generate('ibexa.rest.load_content_type_group_list')
        );
        $generator->endAttribute('href');

        $generator->startList('ContentTypeGroup');
        foreach ($data->contentTypeGroups as $contentTypeGroup) {
            $visitor->visitValueObject($contentTypeGroup);
        }
        $generator->endList('ContentTypeGroup');

        $generator->endObjectElement('ContentTypeGroupList');
    }
}

class_alias(ContentTypeGroupList::class, 'EzSystems\EzPlatformRest\Server\Output\ValueObjectVisitor\ContentTypeGroupList');
