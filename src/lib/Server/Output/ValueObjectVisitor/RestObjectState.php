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
 * RestObjectState value object visitor.
 */
class RestObjectState extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Contracts\Rest\Output\Visitor $visitor
     * @param \Ibexa\Contracts\Rest\Output\Generator $generator
     * @param \Ibexa\Rest\Values\RestObjectState $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('ObjectState');
        $visitor->setHeader('Content-Type', $generator->getMediaType('ObjectState'));
        $visitor->setHeader('Accept-Patch', $generator->getMediaType('ObjectStateUpdate'));

        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ibexa.rest.load_object_state',
                ['objectStateGroupId' => $data->groupId, 'objectStateId' => $data->objectState->id]
            )
        );
        $generator->endAttribute('href');

        $generator->startValueElement('id', $data->objectState->id);
        $generator->endValueElement('id');

        $generator->startValueElement('identifier', $data->objectState->identifier);
        $generator->endValueElement('identifier');

        $generator->startValueElement('priority', $data->objectState->priority);
        $generator->endValueElement('priority');

        $generator->startObjectElement('ObjectStateGroup');

        $generator->startAttribute(
            'href',
            $this->router->generate('ibexa.rest.load_object_state_group', ['objectStateGroupId' => $data->groupId])
        );
        $generator->endAttribute('href');

        $generator->endObjectElement('ObjectStateGroup');

        $generator->startValueElement('defaultLanguageCode', $data->objectState->defaultLanguageCode);
        $generator->endValueElement('defaultLanguageCode');

        $generator->startValueElement('languageCodes', implode(',', $data->objectState->languageCodes));
        $generator->endValueElement('languageCodes');

        $this->visitNamesList($generator, $data->objectState->getNames());
        $this->visitDescriptionsList($generator, $data->objectState->getDescriptions());

        $generator->endObjectElement('ObjectState');
    }
}

class_alias(RestObjectState::class, 'EzSystems\EzPlatformRest\Server\Output\ValueObjectVisitor\RestObjectState');
