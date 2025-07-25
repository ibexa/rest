<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\URLAlias as URLAliasValue;
use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;

/**
 * URLAlias value object visitor.
 */
class URLAlias extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\URLAlias $data
     */
    public function visit(Visitor $visitor, Generator $generator, mixed $data): void
    {
        $generator->startObjectElement('UrlAlias');
        $visitor->setHeader('Content-Type', $generator->getMediaType('UrlAlias'));
        $this->visitURLAliasAttributes($generator, $data);
        $generator->endObjectElement('UrlAlias');
    }

    /**
     * Serializes the given $urlAliasType to a string representation.
     */
    protected function serializeType(int $urlAliasType): string
    {
        switch ($urlAliasType) {
            case URLAliasValue::LOCATION:
                return 'LOCATION';

            case URLAliasValue::RESOURCE:
                return 'RESOURCE';

            case URLAliasValue::VIRTUAL:
                return 'VIRTUAL';
        }

        throw new \RuntimeException("Unknown URL alias type: '{$urlAliasType}'.");
    }

    protected function visitURLAliasAttributes(Generator $generator, URLAliasValue $data): void
    {
        $generator->startAttribute(
            'href',
            $this->router->generate('ibexa.rest.load_url_alias', ['urlAliasId' => $data->id])
        );
        $generator->endAttribute('href');

        $generator->startAttribute('id', $data->id);
        $generator->endAttribute('id');

        $generator->startAttribute('type', $this->serializeType($data->type));
        $generator->endAttribute('type');

        if ($data->type === URLAliasValue::LOCATION) {
            $generator->startObjectElement('location', 'Location');
            $generator->startAttribute(
                'href',
                $this->router->generate('ibexa.rest.load_location', ['locationPath' => $data->destination])
            );
            $generator->endAttribute('href');
            $generator->endObjectElement('location');
        } else {
            $generator->startValueElement('resource', $data->destination);
            $generator->endValueElement('resource');
        }

        $generator->startValueElement('path', $data->path);
        $generator->endValueElement('path');

        $generator->startValueElement('languageCodes', implode(',', $data->languageCodes));
        $generator->endValueElement('languageCodes');

        $generator->startValueElement(
            'alwaysAvailable',
            $this->serializeBool($generator, $data->alwaysAvailable)
        );
        $generator->endValueElement('alwaysAvailable');

        $generator->startValueElement(
            'isHistory',
            $this->serializeBool($generator, $data->isHistory)
        );
        $generator->endValueElement('isHistory');

        $generator->startValueElement(
            'forward',
            $this->serializeBool($generator, $data->forward)
        );
        $generator->endValueElement('forward');

        $generator->startValueElement(
            'custom',
            $this->serializeBool($generator, $data->isCustom)
        );
        $generator->endValueElement('custom');
    }
}
